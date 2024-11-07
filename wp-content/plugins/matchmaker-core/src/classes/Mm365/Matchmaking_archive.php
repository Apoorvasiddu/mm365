<?php

namespace Mm365;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class Matchmaking extends Helpers
{
    use MatchrequestAddon;
    use NotificationAddon;

    function __construct()
    {
        add_filter('mm365_match_findsuppliers', array($this, 'find_matchingcomapnies'), 1 , 1);
    }

    /**--------------------------------------------------
      * Find the matches
      * Search against company database and find matching 
      * companies for the request
      * -- Approach --
      * ++ First Step ++
      * Full text index search on 'Company description' meta 
      * with the keywords from match request. Collect all the
      * matching company ids
      *
      * ++ Second Step ++
      * Get all the company data using the ids from the first
      * step. Loop through the result set and allocate scores
      * to each parameter matched.
      *
      * Skip 'requester' company and 'Buyer' companies from result
      * 
      * Sort the company info based on collective score. Check 
      * threshold score and prepare the mathed companies list
      *
      * Save the list
      ---------------------------------------------------
    */

    /**
     * @param int $mr_id - ID of post saved in mm365_matchrequests post type
     * 
     * 
     */

    function find_matchingcomapnies($mr_id)
    {

        //Match Parameters score values
        $mp_naicscode = $this->mm365_get_option('mm365_match_parameter_naicscode');

        $mp_services = $this->mm365_get_option('mm365_match_parameter_services');
        $mp_industries = $this->mm365_get_option('mm365_match_parameter_industries');
        $mp_location = $this->mm365_get_option('mm365_match_parameter_location');

        $mp_country = ($mp_location / 2);
        $mp_state = ($mp_location / 2);

        $mp_employeecount = $this->mm365_get_option('mm365_match_parameter_employeecount');
        $mp_companysize = $this->mm365_get_option('mm365_match_parameter_companysize');
        $mp_certifications = $this->mm365_get_option('mm365_match_parameter_certifications');
        $mp_minorityclass = $this->mm365_get_option('mm365_match_parameter_minorityclass');
        $mp_intassi = $this->mm365_get_option('mm365_match_parameter_intassi');

        //Keyword Score
        $mp_companydescription = $this->mm365_get_option('mm365_match_parameter_companydescription');

        //results count
        $mp_results_count = $this->mm365_get_option('mm365_match_parameter_resultscount');


        //Match request users submitted Parameters

        //kEYWORDS
        $search_details = get_post_meta($mr_id, 'mm365_services_details', true);

        //lOCATION
        $search_state = get_post_meta($mr_id, 'mm365_service_needed_state');
        $search_country = get_post_meta($mr_id, 'mm365_service_needed_country');


        //ADVANCED SEARCH PARAMATERS
        $search_service_lookingfor = get_post_meta($mr_id, 'mm365_services_looking_for');
        $industry = get_post_meta($mr_id, 'mm365_services_industry');
        $industry_req = get_post_meta($mr_id, 'mm365_services_industry');
        $minority_codes = get_post_meta($mr_id, 'mm365_mr_mbe_category');
        $search_certifications = get_post_meta($mr_id, 'mm365_certifications');
        $naics_codes = get_post_meta($mr_id, 'mm365_naics_codes');
        $search_employees = get_post_meta($mr_id, 'mm365_number_of_employees', true);
        $search_companysize = get_post_meta($mr_id, 'mm365_size_of_company', true);
        $intassi_looking_for = get_post_meta($mr_id, 'mm365_match_intassi_lookingfor');


        //Skip the requester company
        $requester_comp_id = get_post_meta($mr_id, 'mm365_requester_company_id', true);

        //Approval type (Auto or Manual)
        $approval_type = get_post_meta($mr_id, 'mm365_approval_type', true);
        /** Auto approval has been discontinues since v1.6. The feature will remain in code base */

        //Match making
        $score_map = array();
        $keyword_score = array();
        $possible_matches = array();
        $combined_score = array();
        $naics_code_matched = [];
        $companies_with_matching_naics = [];

        /**
         * 
         * 
         */
        $naics_matching_args = array(
            'post_type'  => 'mm365_companies',
            'posts_per_page' => -1,
             'meta_query' => array(
                          array(
                              'key'     => 'mm365_naics_codes',
                              'value'   =>  array_filter( $naics_codes, 'strlen' ),
                              'compare' => 'IN',
                          )
                      )
        );
        $naics_matching = new \WP_Query($naics_matching_args);

        if ($naics_matching->have_posts()) {
            while ($naics_matching->have_posts()):
                $naics_matching->the_post();
                
                 array_push($naics_code_matched, get_the_ID());
                 array_push($companies_with_matching_naics, array('score' => $mp_naicscode,'post_id' => get_the_ID(),));
            endwhile;
           
        }else{
          
            //Abort
            update_post_meta($mr_id, 'mm365_matchrequest_status', 'nomatch');
            update_post_meta($mr_id, 'mm365_matched_companies', '');

            //Email the user about no match
            $this->mm365_notfication_nomatch($mr_id);
            return true;

        }


        /**
         * 
         * First query to find the key word matches from company description and list them for second query to loop through
         * 
         */

        //$purged = mm365_pharase_finder($search_details);

        $purged = '';
        $strict = '';
        global $wpdb;

        //2.0 Onwards revised keyword patterning
        $searched_keywords = explode(',', $search_details);
        foreach ($searched_keywords as $keyword) {
            $purged .= '' . $this->keyword_cleanser($keyword) . ' ';
            $strict .= '"' . $this->keyword_cleanser($keyword) . '" ';
        }

      

        /**
         * Find companies with matching naics codes
         * 
         */

        /**
         * 
         * 
         * Method - 1
         * Use strict and non-strict search and combine the result
         * 
         * Method - 2
         * User inputing quotes
         * 
         * 
         */
 

        //Search in company description
        $sql_description = "SELECT MATCH(meta_value) AGAINST('" . $purged . "') AS score, post_id FROM " . $wpdb->prefix . "postmeta 
                        WHERE MATCH(meta_value) AGAINST('" . $purged . "') AND  `meta_key`= 'mm365_company_description'
                        AND post_id IN (" . implode(",", array_map("intval", $naics_code_matched)) . ") ORDER BY SCORE DESC LIMIT " . $mp_results_count . ";";

        //Search in post title
        $sql_title = "SELECT  MATCH(post_title) AGAINST('" . $purged . "') AS score, ID as post_id
                        FROM " . $wpdb->prefix . "posts WHERE post_type = 'mm365_companies' AND ( MATCH(post_title) AGAINST('" . $purged . "') >1) 
                        AND ID  IN (" . implode(",", array_map("intval", $naics_code_matched)) . ")  ORDER BY SCORE DESC LIMIT 9999999" ;

        //Search keywords as phrases
        $sql_phrase_search = "SELECT MATCH(meta_value) AGAINST('" . $strict . "') AS score, post_id FROM " . $wpdb->prefix . "postmeta 
       WHERE MATCH(meta_value) AGAINST('" . $strict . "') AND  `meta_key`= 'mm365_company_description' 
       AND post_id  IN (" . implode(",", array_map("intval", $naics_code_matched)) . ") 
       ORDER BY SCORE DESC LIMIT 9999999";



        //Company description
        $company_desc_results = $wpdb->get_results($sql_description);

        //Company title
        $company_title_results = $wpdb->get_results($sql_title);

        //Company description strict mode
        $company_desc_strict_results = $wpdb->get_results($sql_phrase_search);

        /**
         * 
         * Full text index searching on Company Title, Key Words in descrition with wild search 
         * and strict mode search (in "") combine the result set and select the company with 
         * maximum score
         * 
         */


        //Keyword match combined (Comapny description and title ) - $company_desc_results,$company_title_results,$company_desc_strict_results
        $all_keyword_matahced_companies = array_merge($company_title_results,$company_desc_results,$company_desc_strict_results);
        $keywordscores_array = json_decode(json_encode($all_keyword_matahced_companies), true);

   

    


        $primary_result_set = $this->mergeCompanyArrays($keywordscores_array, $companies_with_matching_naics);

        //Remove all company records which doesnot have a subscription( mm365_subscription_status  - expired/not_subscribed)
    

         $tally_keyword_naicscore = array();
        
        // Loop through the original array
        foreach ($primary_result_set as $item) {
            $post_id = $item["post_id"];
            $score = $item["score"];
            
            // If the post_id is already in the tally array, add the score to it
            if (isset($tally_keyword_naicscore[$post_id])) {
                $tally_keyword_naicscore[$post_id] += $score;
            } else {
                // Otherwise, initialize the tally for this post_id
                $tally_keyword_naicscore[$post_id] = $score;
            }
        }



        //Finding all the companies to look for
        $max_score = 0;
        foreach ($tally_keyword_naicscore as $key => $value) {

            $score = $value;
            $minInput = 0.001;
            $maxInput = 200;
            $minOutput = 1;
            $maxOutput = 40;
            
            $mappedScore = $this->mapScore($score, $minInput, $maxInput, $minOutput, $maxOutput);
            


            //Assign max score
            if ($value > $max_score) {
                $max_score = $value;
            }



            //Devide the percentage match by two and add with other side (V x MAXSCORE / 100)
            $match_percentage = number_format(($value / $max_score) * 100, 0);

            //echo $match_percentage."<br/>";
            if ($match_percentage > 0) {
                //array_push($score_map, array("c"=>$key->post_id,"s"=>($match_percentage * $mp_companydescription / 100)));
                array_push($combined_score, array("c" => $key, "s" => $mappedScore));
                array_push($keyword_score, array("c" => $key, "s" => $mappedScore));
                //Adding matched companies for second query                            
                array_push($possible_matches, $key);
            }
        }
        
   
       
        /*------------------------------------------------ADDITIONAL META SEARCH --------------------------------------------- */
        //Ignore other and push other to main array
        if (in_array('other', $search_service_lookingfor)) {
            $other_services = (array_search('other', $search_service_lookingfor) + 1);
            $oth_services_requested = explode(",", $search_service_lookingfor[$other_services]);
            array_splice($search_service_lookingfor, -2);
            foreach ($oth_services_requested as $key => $value) {
                array_push($search_service_lookingfor, $value);
            }
        }

        $serv_array = array("relation" => "OR");
        foreach ($search_service_lookingfor as $key => $value) {
            array_push(
                $serv_array,
                array(
                    'key' => 'mm365_services',
                    'value' => $value,
                    'compare' => 'LIKE',
                )
            );
        }

        //Ignore other and push other to main array
        if (in_array('other', $industry)) {
            $other_industry = (array_search('other', $industry) + 1);
            $oth_industry_requested = explode(",", $industry[$other_industry]);
            array_splice($industry, -2);
            foreach ($oth_industry_requested as $key => $value) {
                array_push($industry, $value);
            }
        }

        $indus_array = array("relation" => "OR");
        foreach ($industry as $key => $value) {
            array_push(
                $indus_array,
                array(
                    'key' => 'mm365_industry',
                    'value' => $value,
                    'compare' => 'LIKE',
                )
            );
        }


        //Ignore other and push other to main array
        if (in_array('other', $search_certifications)) {
            $other_certifications = (array_search('other', $search_certifications) + 1);
            $oth_certifications_requested = explode(",", $search_certifications[$other_certifications]);
            array_splice($search_certifications, -2);
            foreach ($oth_certifications_requested as $key => $value) {
                array_push($search_certifications, $value);
            }
        }
        $certifications_array = array('relation' => 'OR');
        $certif = array();
        foreach ($search_certifications as $key => $value) {
            array_push($certif, $value);
        }
        array_push(
            $certifications_array,
            array(
                'key' => 'mm365_certifications',
                'value' => $certif,
                'compare' => 'LIKE',
            )
        );


        //NAICS Codes
        $naics_codes_array = array('relation' => 'OR');
        if (!empty($naics_codes)) {
            foreach ($naics_codes as $key => $value) {
            }
            array_push(
                $naics_codes_array,
                array(
                    'key' => 'mm365_naics_codes',
                    'value' => $value,
                    'compare' => 'LIKE',
                )
            );
        }


        //Minority Categories
        $minority_codes_array = array('relation' => 'OR');
        if (!empty($minority_codes)) {
            $micode = array();
            foreach ($minority_codes as $key => $value) {
                array_push($micode, $value);
            }
            array_push(
                $minority_codes_array,
                array(
                    'key' => 'mm365_minority_category',
                    'value' => $micode,
                    'compare' => 'LIKE',
                )
            );
        }
        // if(is_numeric($search_city)){ $city_match    =  array('key' => 'mm365_company_city','value'=> $search_city,'compare' => '='); }else{ $city_match = array(); }
        // if(is_numeric($search_state)){ $state_match   =  array('key' => 'mm365_company_state','value'=> $search_state,'compare' => '='); }else{ $state_match = array(); }
        // if(is_numeric($search_country)){ $country_match =  array('key' => 'mm365_company_country','value'=> $search_country,'compare' => '='); }else{ $country_match = array(); }

        if ($search_employees != '') {
            $employee_match = array('key' => 'mm365_number_of_employees', 'value' => $search_employees, 'compare' => '=');
        } else {
            $employee_match = array();
        }
        if ($search_companysize != '') {
            $companysize_match = array('key' => 'mm365_size_of_company', 'value' => $search_companysize, 'compare' => '=');
        } else {
            $companysize_match = array();
        }

        /*------------------------------------------------ADDITIONAL META SEARCH ends--------------------------------------------- */
        /**
         * 
         * The search works based on Keywords matched in Company descriptiona nd company name. Rest of the parameters will only
         * be searched in to the companies that has matching keywords in description or title
         * 
         */

        if (count($possible_matches) > 0){
           
            /**
             * 2.6 Onwards
             * Add meta query to ignore companies that has expired subscription or without a subscription
             * 
             */

            //Final query argument for prefilled data
            $search_args = array(
                'post_type' => 'mm365_companies',
                'post_status' => 'publish',
                'post__in' => $possible_matches,
                'posts_per_page' => -1,
            );

            $services_base_count = count($search_service_lookingfor);
            $industry_base_count = count($industry);
            $certification_base_count = count($search_certifications);
            $int_assi_base_count = count($intassi_looking_for);


            $states_searching_count = count($search_state);
            $countries_searching_count = count($search_country);

            if ($countries_searching_count > 0) {
                $each_country_match_score = ($mp_country / $countries_searching_count);
            } else
                $each_country_match_score = $mp_country;

            //echo $countries_searching_count."countries, each gets".$each_country_match_score."<br/>";

            //Each state matched gets the following score
            if ($states_searching_count > 0) {
                $each_state_match_score = ($mp_state / $states_searching_count);
            } else
                $each_state_match_score = $mp_state;


            //If user selected 'All states' of a particular countries filter them out
            $all_states = $search_state;
            foreach ($all_states as $key => $value) {
                if (is_numeric($value)) {
                    unset($all_states[$key]);
                }
            }

            //Filter valid state ids which user is looking for
            foreach ($search_state as $key => $value) {
                if (!is_numeric($value)) {
                    unset($search_state[$key]);
                }
            }


            //Loop through posible matches for weightage
            $search_loop = new \WP_Query($search_args);
            if ($search_loop->have_posts()) {
                while ($search_loop->have_posts()):
                    $search_loop->the_post();

                    //Match Service
                    $services_score = 0;
                    //
                    if ($services_base_count >= '1' or !empty($search_service_lookingfor)) {
                        $company_service = get_post_meta(get_the_ID(), 'mm365_services');
                        $each_service_matched = ($mp_services / $services_base_count);
                        foreach ($search_service_lookingfor as $key => $value) {
                            if (in_array($value, $company_service)) {
                                $services_score = $services_score + $each_service_matched;
                            } else {
                                $services_score = 0;
                            }
                        }
                    } else {
                        $services_score = $mp_services;
                    }

                    //Match Industry
                    $industry_score = 0;
                    //
                    if ($industry_base_count >= '1' or !empty($industry)) {
                        $company_industry = get_post_meta(get_the_ID(), 'mm365_industry');
                        $each_industry_matched = ($mp_industries / $industry_base_count);
                        foreach ($industry as $key => $value) {
                            if (in_array($value, $company_industry)) {
                                $industry_score = $industry_score + $each_industry_matched;
                            } else {
                                $industry_score = 0;
                            }
                        }
                    } else {
                        $industry_score = $mp_industries;
                    }

                    //Match minority codes
                    $minoprity_score = 0;
                    //
                    if ($minority_codes != '' or !empty($minority_codes)) {
                        $minority_category = get_post_meta(get_the_ID(), 'mm365_minority_category', true);
                        foreach ($minority_codes as $key => $value) {
                            if ($value == $minority_category) {
                                $minoprity_score = $mp_minorityclass;
                            } else {
                                $minoprity_score = 0;
                            }
                        }
                    } else {
                        $minoprity_score = $mp_minorityclass;
                    } //Add score to all results if not specified


                    //Match Location - 


                    /**
                     * 
                     * Logic
                     * Get the array of serviceable states and countries from company data 
                     * and intercet the same with match request data. Find the number of matched items
                     * and multiply the same with parameter score to produce the final score
                     * 
                     */

                    //Comapnies Providing their services at
                    $cmp_servicable_countries = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_countries');
                    $cmp_servicable_states = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_states');

                    $countries_score = 0;
                    $states_score = 0;

                    //If companies are providing their services to selected countries
                    if (!empty($search_country)) {

                        $countries_matched = count(array_intersect($search_country, $cmp_servicable_countries));

                        $countries_score = ($each_country_match_score * $countries_matched);

                        //If this company is providing services to all states of a country which the user was looking at
                        foreach ($search_country as $country_id) {
                            $search_company_allstates[] = 'all_' . $country_id . '_states';
                        }

                        //Comanies providing services to all states match
                        $all_states_matched = count(array_intersect($search_company_allstates, $cmp_servicable_states));
                        if ($all_states_matched > 0) {
                            $states_score += ($each_state_match_score * $all_states_matched);
                        }

                    }


                    //If companies are providing their services to selected states 
                    if (!empty($search_state)) {

                        $states_matched = count(array_intersect($search_state, $cmp_servicable_states));

                        $states_score += ($each_state_match_score * $states_matched);

                    }

                    //if buyer is has chosen any states of a specific country
                    if (!empty($all_states)) {
                        foreach ($all_states as $country_id) {
                            $country_associated = explode("_", $country_id);
                            if (isset($country_associated[1])):
                                if (in_array($country_associated[1], $cmp_servicable_countries)) {
                                    $states_score += $each_state_match_score;
                                }
                            endif;
                        }
                    }



                    //Add score to all results if no specific location is selected
                    if ($countries_searching_count == 0 and $states_searching_count == 0) {
                        $countries_score = $each_country_match_score;
                        $states_score = $each_state_match_score;
                    }


                    $company_location_score = ($countries_score + $states_score);


                    //Match Employee Count 
                    if ($search_employees != ''):
                        if ($search_employees == get_post_meta(get_the_ID(), 'mm365_number_of_employees', true)) {
                            $empl_score = $mp_employeecount;
                        } else {
                            $empl_score = 0;
                        }
                    else:
                        $empl_score = $mp_employeecount;
                    endif;

                    //Match Company Size 
                    if ($search_companysize != ''):
                        if ($search_companysize == get_post_meta(get_the_ID(), 'mm365_size_of_company', true)) {
                            $comp_score = $mp_companysize;
                        } else {
                            $comp_score = 0;
                        }
                    else:
                        $comp_score = $mp_companysize;
                    endif;


                    if ($search_employees == '') {
                        $empl_score = $mp_employeecount;
                    }
                    if ($search_companysize == '') {
                        $comp_score = $mp_companysize;
                    }

                    //Match NAICS code 
                    $naics_score = 0;
                    $company_naics = get_post_meta(get_the_ID(), 'mm365_naics_codes');
                    $looking_naics = get_post_meta($mr_id, 'mm365_naics_codes');

                    if (count($looking_naics) > 0) {
                        $each_naics_matched = ($mp_naicscode / count($looking_naics));
                        foreach ($looking_naics as $key => $value) {
                            if (in_array($value, $company_naics)) {
                                $naics_score = $naics_score + $each_naics_matched;
                            } else {
                                $naics_score = 0;
                            }
                        }
                    } else {
                        $naics_score = $mp_naicscode;
                    } //Add score to all results if not specified

                    //Certification match
                    $certification_score = 0;
                    $company_certification = get_post_meta(get_the_ID(), 'mm365_certifications');
                    if ($certification_base_count > 0) {
                        $each_certification_matched = ($mp_certifications / $certification_base_count);
                        foreach ($search_certifications as $key => $value) {
                            if (in_array($value, $company_certification)) {
                                $certification_score = $certification_score + $each_certification_matched;
                            } else {
                                $certification_score = 0;
                            }
                        }
                    } else {
                        $certification_score = $mp_certifications;
                    } //Add score to all results if not specified


                    //Match internation services looking for
                    $int_assi_score = 0;
                    $int_assi_company = get_post_meta(get_the_ID(), 'mm365_international_assistance');
                    if ($int_assi_base_count > 0) {
                        $each_int_assi_matched = ($mp_intassi / $int_assi_base_count);
                        $intassi_score = 0;
                        foreach ($intassi_looking_for as $key => $value) {
                            if (in_array($value, $int_assi_company)) {
                                $intassi_score = $intassi_score + $each_int_assi_matched;
                            } else {
                                $intassi_score = 0;
                            }
                        }
                    } else {
                        $intassi_score = $mp_intassi;
                    } //Add score to all results if not specified


                    //Total Score per result ()
                    $total = ($services_score +
                        $industry_score +
                        $minoprity_score +
                        $company_location_score +
                        $empl_score +
                        $comp_score +
                        //$naics_score +
                        $certification_score +
                        $intassi_score
                    );

                    array_push($score_map, array("c" => get_the_ID(), "s" => $total));
                    array_push($combined_score, array("c" => get_the_ID(), "s" => $total));

                endwhile;
            } else
                $score_map = array();

            //If no posts

        }

        //die(); //for debugging remve later

        if (is_numeric($mp_results_count) and $mp_results_count > 0) {
            $limit = 'LIMIT ' . $mp_results_count;
        } else
            $limit = '';

        //Combine all scores
        $temp = [];
        if (!empty($score_map)) {
            foreach ($score_map as $value) {
                //check if element exists in the temp array
                if (!array_key_exists($value['c'], $temp)) {
                    //if it does not exist, create it with a value of 0
                    $temp[$value['c']] = 0;
                }
                //Add up the values from each element
                $temp[$value['c']] += $value['s'];
            }
        }



        //Score hint
        $ovrl_score = [];
        if (!empty($combined_score)) {
            foreach ($combined_score as $value) {
                //check if element exists in the temp array
                if (!array_key_exists($value['c'], $ovrl_score)) {
                    //if it does not exist, create it with a value of 0
                    $ovrl_score[$value['c']] = 0;
                }
                //Add up the values from each element
                $ovrl_score[$value['c']] += $value['s'];
            }
        }

        //Sorting array with H to L order
        arsort($temp);
        arsort($ovrl_score);




        //Approval type based switching
        $newArray = array();
        if (!empty($score_map)) {
            //foreach($temp
            foreach ($ovrl_score as $key => $value) {
                if ($value >= 0) {
                    //Adding ID to array with match approval status value
                    $service_typ = get_post_meta($key, 'mm365_service_type', true);
                    if ($key != $requester_comp_id and (get_post_status($key) == 'publish') and $service_typ == 'seller') {

                        //Check subscription
                        if (!in_array(get_post_meta($key, 'mm365_subscription_status', true), array("Expired", "Not Subscribed"))) {
                            //Auto approve or keep it pending (yes-approval required, no-auto approved)
                            if ($approval_type == 'yes') {
                                array_push($newArray, array($key, "0"));
                            } else {
                                array_push($newArray, array($key, "1"));
                            }

                        }
                       

                    }
                }
            }
        }

        //Limiting results - optional
        $result_array = $newArray;

  

        //Recording the matches
        if (empty($result_array) or (count($result_array) == 0)) {
            update_post_meta($mr_id, 'mm365_matchrequest_status', 'nomatch');
            update_post_meta($mr_id, 'mm365_matched_companies', '');

            //Email the user about no match
            $this->mm365_notfication_nomatch($mr_id);
            return true;
        } else {
            update_post_meta($mr_id, 'mm365_matched_companies', maybe_serialize($result_array));
            update_post_meta($mr_id, 'mm365_matched_companies_scores', maybe_serialize($ovrl_score));
            //Match approval preffered
            if (!isset($approval_type) or $approval_type == 'yes') {
                update_post_meta($mr_id, 'mm365_matchrequest_status', 'pending');
            } else {
                update_post_meta($mr_id, 'mm365_matchrequest_status', 'auto-approved');
                $data = array(
                    'ID' => $mr_id,
                    'post_content' => "",
                    'meta_input' => array('mm365_matched_companies_approved_time' => time())
                );
                wp_update_post($data);
            }
            return true;
        }


        //FUNCTION ENDs
    }


    /**
     * 
     * 
     * 
     */

     function mm365_notfication_nomatch($mr_id) {

        //Trigger along with match request send if no match is displayed
        $user_id           = get_post_meta( $mr_id, 'mm365_requester_id', true );
        $request_details   = get_post_meta( $mr_id, 'mm365_services_details', true );
        $requester_comp_id = get_post_meta( $mr_id, 'mm365_requester_company_id', true );
        
        //User info
        $user_name  = get_post_meta( $requester_comp_id, 'mm365_contact_person', true );
        $user_email = get_post_meta( $requester_comp_id, 'mm365_company_email', true );
    
        //Send to all mmsdc_magaer user roles
        $to   = $user_email;
        $link = site_url().'/request-for-match/';
        $subject     = 'No companies matched against your request';
    
        //Mail Body
        $title       = 'No matches found!';
        $content     = '
                    <p>Hi '.$user_name.',</p>
                    <p><strong>Match Request Description:</strong></p>
                    <p style="font-style:italic;">"'.$request_details.'"</p>
                    <p>
                    Your match request produced no matches. Please refine your search by selecting as many drop down details 
                    as possible and by providing more specific details in the description field.
                    </p>
                    <p>Please click on the below button to login and edit your match request.</p>
                '; 
        
    
        $body        = $this->mm365_email_body($title,$content,$link,'Edit Match Request');
        $headers     = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( $to, $subject, $body, $headers );
    
    
    }

}
<?php
namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}



class CompanySearch
{
    use MatchrequestAddon;
    use CertificateAddon;
    use CompaniesAddon;
    use CouncilAddons;
    use CountryStateCity;

    function __construct(){

        //Load assets
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        //Search and find buyer
        add_action( 'wp_ajax_find_company', array( $this, 'find_company' ) );

        add_filter('mm365_search_and_download_companies', array($this,'find_and_download_companies'), 11, 4);


    }

    /*---------------------------------------------
    * Assets
    ----------------------------------------------*/
    function assets(){

        if ( wp_register_script( 'mm365_search_company',plugins_url('matchmaker-core/assets/mm365_search_company.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_search_company' );
            wp_localize_script( 'mm365_search_company', 'searchCompaniesAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("searchCompanies_ajax_nonce")
            ) );
          
        }    

    }

    /**
     * 
     * Search and download - uses search logic similar to math request
     * 
     * @param $keyword
     * @param $council
     * @param $service_type - buyer/supplier
     * @param $additional_filter ARRAY
     *        minority-category, services, industries, city, state, country, naics_codes, employee_count, company_size
     * 
     */

    public function core_search($keyword, $council, $service_type, $additional_filter = array()){

            global $wpdb;

            //services filter
            $services = array();
            if(!empty($additional_filter['services'])){
              $services = $additional_filter['services'];
              if(in_array('other',$services))
              array_push($services,$additional_filter['other_services']);
            }

            //services filter
            $serv_array = array();
            if(!empty($additional_filter['services'])):
                $serv_array = array(
                                    'key'     => 'mm365_services',
                                    'value'   => $additional_filter['services'],
                                    'compare' => 'IN',
                              );
            endif;

            //Industries Filter

            $indus_array = array();
            $indstry = array();
            if(isset($_POST['industry'])){
              $indstry = $additional_filter['industry'];
              if(in_array('other',$indstry))
              array_push($indstry,$additional_filter['other_industry']);
            }
            if(!empty($indstry)) {
              $indus_array = array(
                    'key'     => 'mm365_industry',
                    'value'   => $indstry,
                    'compare' => 'IN',
              );
            }

            //Minority category filter
            $minoritycategory_array = array();
            if(!empty($additional_filter['minority_category'])):
                $minoritycategory_array = array(
                                    'key'     => 'mm365_minority_category',
                                    'value'   => $additional_filter['minority_category'],
                                    'compare' => '=',
                            );
            endif;

            /** Search Employee Count */
            if($additional_filter['search_employees'] == '< 20'): 
                $ec_search =  array("20 to 50",
                                    "50 to 100",
                                    "100 to 200",
                                    "200 to 500",
                                    "500 to 1000",
                                    "1000+"); 
                $ec_compare = "NOT IN";
            else:  
                $ec_search = $additional_filter['search_employees']; 
                $ec_compare = "=";
            endif;

            if($ec_search != '' OR !empty($ec_search)){ 
                $employee_match =  array(
                  'key' => 'mm365_number_of_employees',
                  'value'=> $ec_search,
                  'compare' => $ec_compare); 
              }else{ 
                $employee_match = array(); 
              }
          
            /** Search Company size */
            if($additional_filter['search_companysize'] == '<$100,000' OR $additional_filter['search_companysize'] == '< $100,000'): 
                $cs_search =  array("$100,000 - $500,000",
                                    "$500,000 - $1M",
                                    "$1M - $5M",
                                    "$5M - $50M",
                                    "$50M - $200M",
                                    "$200M - $500M",
                                    "$500M - $1B",
                                    "$1B+");
          
                $cs_compare = "NOT IN";
            else:  
                $cs_search = $additional_filter['search_companysize']; 
                $cs_compare = "=";
            endif;

            if(!empty($cs_search)){ 
                $companysize_match =  array(
                                       'key' => 'mm365_size_of_company',
                                       'value'=> $cs_search,
                                       'compare' => $cs_compare); 
            }else{
                 $companysize_match = array(); 
            }

              // Naics code search
              $naics_array = array();
              $naics = array();
              if(isset($additional_filter['naics_codes'])){
                $naics = $additional_filter['naics_codes'];
              }
              $naics_to_search = array_filter( $naics, 'strlen' );
              if(!empty($naics_to_search)) {
                $naics_array = array(
                      'key'     => 'mm365_naics_codes',
                      'value'   =>  $naics_to_search,
                      'compare' => 'IN',
                );
              }

              //certification search
              $certification_array = array();
              $certification = array();
              if(isset($additional_filter['certifications'])){
                $certification = $additional_filter['certifications'];
                if(in_array('other',$certification))
                array_push($certification,$additional_filter['other_certification']);
              }
              if(!empty($certification)) {
                $certification_array = array(
                      'key'     => 'mm365_certifications',
                      'value'   => $certification,
                      'compare' => 'IN',
                );
              }



            //location
            $company_city = $additional_filter['city'];
            $company_state = $additional_filter['state'];
            $company_country =  $additional_filter['country'];

            if(is_numeric($company_city)){ $city_match    =  array('key' => 'mm365_company_city','value'=> $company_city,'compare' => '='); }else{ $city_match = array(); }
            if(is_numeric($company_state)){ $state_match   =  array('key' => 'mm365_company_state','value'=> $company_state,'compare' => '='); }else{ $state_match = array(); }
            if(is_numeric($company_country)){ $country_match =  array('key' => 'mm365_company_country','value'=> $company_country,'compare' => '='); }else{ $country_match = array(); }
        
            //Service type
            if(!empty($service_type) AND $service_type != 'all'){ 
                $service_type_match =  array(
                                            'key' => 'mm365_service_type',
                                            'value'=> $service_type,
                                            'compare' => '='
                                        ); 
            }else{ 
                $service_type_match = array(); 
            }

            
            //Keyword Search

            $fulltext_matched_ids = array();
            $keywordMatch = array();

            // full text search on company desc
            $strict_mode = '"'.$this->keyword_cleanser($keyword).'" ';
            //Search keywords as phrases
            $sql_phrase_search =    "SELECT MATCH(meta_value) AGAINST('".$strict_mode."') as SCORE, post_id FROM ".$wpdb->prefix."postmeta 
            WHERE  MATCH(meta_value) AGAINST('".$strict_mode."') AND `meta_key`= 'mm365_company_description' ORDER BY SCORE DESC LIMIT 9999999"; 

            $fulltext_matched = $wpdb->get_results($sql_phrase_search);

            foreach($fulltext_matched as $ft_comp){
                $fulltext_matched_ids[] = $ft_comp->post_id;
            }

            //Looking for company names

            $q1 = new \WP_Query(array(
                'fields' => 'ids',
                'post_type' => 'mm365_companies',
                's' => $keyword,
                'posts_per_page' => -1, 
            ));
            $keywordMatch = $q1->posts;

            $find_companies = array_unique( array_merge( $keywordMatch, $fulltext_matched_ids ) );




            //If council filtering is active
            if($council != ''){
                
                $q2 = new \WP_Query(array(
                        'fields' => 'ids',
                        'post_type' => 'mm365_companies',
                        'posts_per_page' => -1, 
                        'meta_query' => array(
                            array(
                                'key' => 'mm365_company_council',
                                'value' => $council,
                                'compare' => '='
                            ),
                            $service_type_match
                        )
                ));
                $councilMatch = $q2->posts;

                $keyword_title_search_result = array_unique( array_merge($keywordMatch, $fulltext_matched_ids) );

                $find_companies = array_intersect( $keyword_title_search_result, $councilMatch );

            }



            //search
           
            if(!empty($find_companies)){

                $args = array(
                    'post_type'      => 'mm365_companies',
                    'post_status'    => 'publish',
                    'order'          => 'DESC',
                    'post__in'       => $find_companies,
                    'posts_per_page' => -1,
                    'meta_query'     => array($serv_array, 
                                              $minoritycategory_array,
                                              $certification_array,
                                              $city_match, 
                                              $state_match,
                                              $country_match,
                                              $naics_array,
                                              $employee_match,
                                              $companysize_match,
                                              $service_type_match)
                );

                $companies  = new \WP_Query($args); 

                return $companies;

            }else return NULL;


    }

    /*---------------------------------------------
    * Search and load companies
    ----------------------------------------------*/
    public function find_company(){
     
        $nonce     = $_POST['nonce'];
        if (!wp_verify_nonce( $nonce, 'searchCompanies_ajax_nonce' ) OR !is_user_logged_in()) {
            echo '0';
            die();
        }

        //$certificationClass = new mm365_certification;
        $keyword   = $_POST['search_company'];
        $council   = $_POST['company_council'];
        $service_type = $_POST['service_type'];


        //Search keyword against buyer companies
        if($keyword != ''){

            $additional_filters = array(
                'services' => $_POST['services'],
                'minority_category' => $_POST['minority_category'],
                'city' => $_POST['company_city'],
                'state' => $_POST['company_state'],
                'country' =>$_POST['company_country'],
                'search_employees' => $_POST['number_of_employees'],
                'search_companysize' =>  $_POST['size_of_company'],
                'industry' => $_POST['industry'],
                'certifications' => $_POST['certifications'],
                'naics_codes' => $_POST['naics_codes']
            );

            //$keyword, $council, $service_type, $additional_filters
            $companies =  $this->core_search($keyword, $council, $service_type, $additional_filters);
            //$totalData = $companies->found_posts;
            
            if ( !empty($companies) AND $companies->have_posts() ) {

                echo '<table id="superadmin_searchresult_companies" class="mm365datatable-list table table-striped"  cellspacing="0" width="100%" data-intro="List of companies found">
                <thead class="thead-dark">
                  <tr>
                    <th width="12%" data-intro="Company name"><h6>Company</h6></th>
                    <th data-intro="Company description" class="no-sort" ><h6>Description</h6></th>
                    <th data-intro="Products or services offered by the companies" class="no-sort" ><h6>Products or Services</h6></th>
                    <th data-intro="Company type"><h6>Type</h6></th>
                    <th class="no-sort" data-intro="Associated Council"><h6>Council</h6></th>
                    <th class="no-sort" data-intro="Contact information" ><h6>Contact</h6></th>
                    <th class="no-sort" data-intro="Company\'s address location"><h6>Location</h6></th>                  
                  </tr>
                </thead>
                <tbody>';


                while ( $companies->have_posts() ) {
                    $services = array();
                    echo "<tr>";
                    $companies->the_post();

                    $current_council = get_post_meta( get_the_ID(), 'mm365_company_council', true );
                    $service_type = get_post_meta( get_the_ID(), 'mm365_service_type', true );
                    $show_type = $this->get_company_service_type($service_type);

                    echo "<td>".$this->get_certified_badge(get_the_ID(), true)."<a target='_blank' href='".site_url('view-company').'?cid='.get_the_ID()."'>".get_the_title()."</a></td>";
                    $company_info = get_post_meta('mm365_company_description', get_the_ID(), true); 
                    echo "<td>"; 
                     echo strlen($company_info) > 250 ? substr(wp_strip_all_tags($company_info),0,250)."..." : $company_info;
                    echo"</td>";

                    echo "<td>"; 
                        foreach ((get_post_meta( get_the_ID(), 'mm365_services')) as $key => $value){
                            $services[] = $value;
                        }
                        if(!empty($services)):echo implode( ', ', $services );  else: echo "-"; endif;
                    echo"</td>";

                    echo "<td><span class='cl-".$show_type."'>".$show_type."</span></td>";
                    echo "<td>".$this->get_council_info($current_council)."</td>";
                    echo "<td>";
                     echo '<div class="intable_span">'.get_post_meta( get_the_ID(), 'mm365_contact_person', true ).'</div>';
                     echo '<div class="intable_span">'.get_post_meta( get_the_ID(), 'mm365_company_email', true ).'</div>';
                     echo '<div class="intable_span">'.get_post_meta( get_the_ID(), 'mm365_company_phone', true ).'</div>';
                    echo "</td>";
                    echo "<td>".$this->get_cityname(get_post_meta( get_the_ID(), 'mm365_company_city', true )).$this->get_statename(get_post_meta( get_the_ID(), 'mm365_company_state', true )).", ".$this->get_countryname(get_post_meta( get_the_ID(), 'mm365_company_country', true ))."</td>";
                    echo "</tr>";
                }

                echo '</tbody>
                </table>';
                
            }else{
                echo 'no-match';
            }


        }

        wp_die();

    }


   /*---------------------------------------------
    * Search and download companies
    ----------------------------------------------*/
    public function find_and_download_companies($keyword, $council, $service_type, $additional_filters){

            if($keyword != ''){

                //Find and search
                $data = array();

                $get_companies =  $this->core_search($keyword, $council, $service_type, $additional_filters);


                //Loop through
                if ( !empty($get_companies) AND $get_companies->have_posts() ) {
                  
                    //Loop through companies
                    while ( $get_companies->have_posts() ) {
                       $get_companies->the_post();
                            //services
                            $ar_services_list = (get_post_meta(get_the_ID(),'mm365_services' ));
                            if(!empty($ar_services_list)){
                            $services = array();
                            foreach ($ar_services_list as $key => $value) {
                                $services[] = $value;
                            }
                            if(isset($services)):
                                //$services_list =  implode( ', ', $services );
                                $services_list = '';
                                foreach ($services as $service) {
                                    $services_list .= "\n".'• '.$service.'';
                                }      
                            endif;
                            $services = array();
                            }else{
                                    $services_list = '-';
                            }

                            //Industries
                            if(!empty((get_post_meta( get_the_ID(), 'mm365_industry' )))):
                            foreach ((get_post_meta( get_the_ID(), 'mm365_industry' )) as $key => $value) {
                                $industries[] = $value;
                            }
                            if(isset($industries)): 
                                //$industries_list =  implode( ', ', $industries );
                                $industries_list = '';
                                foreach ($industries as $industry) {
                                    $industries_list .= "\n".'• '.$industry.'';
                                }
                            endif;
                            $industries = array();
                            else:
                            $industries_list = '-';
                            endif;

                            //Location
                            $city    = $this->get_cityname(get_post_meta( get_the_ID(), 'mm365_company_city', true ),"");
                            $state   = $this->get_statename(get_post_meta( get_the_ID(), 'mm365_company_state', true ));
                            $country = $this->get_countryname(get_post_meta( get_the_ID(), 'mm365_company_country', true ));

                            //Company size
                            $employee_count = get_post_meta( get_the_ID(), 'mm365_number_of_employees', true );
                            if($employee_count == '&lt; 20'): $ec ="< 20"; else:  $ec = $employee_count; endif;
                            $size = get_post_meta( get_the_ID(), 'mm365_size_of_company', true );
                            if($size == '&lt;$100,000'): $company_size ="< $100,000"; else:  $company_size = $size; endif;

                            //Certification
                            $ar_certification = get_post_meta(get_the_ID(),'mm365_certifications');
                            if(!empty($ar_certification)){
                            $certifications = array();
                            foreach ($ar_certification as $key => $value) {
                                $certifications[] = $value;
                            }
                            if(isset($certifications)):
                                //$certifications_list =  implode( ', ', $certifications );
                                $certifications_list = '';
                                foreach ($certifications as $certificate) {
                                    $certifications_list .= "\n".'• '.$certificate.'';
                                }
                            endif;
                            $certifications = array();
                            }else{
                            $certifications_list = '-';
                            }

                            //Curent customers
                            $mc = json_decode(get_post_meta( get_the_ID(), 'mm365_main_customers', true ));
                            if(!empty($mc)):
                            $current_customers = array();
                            foreach ($mc as $key => $value) {
                                if($value != '') { array_push($current_customers,$value); }
                            }
                            //$customers   =  implode( ', ', $current_customers);
                            $customers = '';
                            foreach ($current_customers as $customer) {
                                    $customers .= "\n".'• '.$customer.'';
                            }
                            else:
                            $customers = '-';
                            endif;

                            //NAICS Codes
                            $naics = array();
                            if(!empty((get_post_meta( get_the_ID(), 'mm365_naics_codes' )))):
                            foreach ((get_post_meta( get_the_ID(), 'mm365_naics_codes')) as $key => $value){
                                $naics[] = $value;
                            }
                            if(isset($naics)):
                                //$naics_list = implode( ', ', $naics );
                                $naics_list = '';
                                foreach ($naics as $naic) {
                                if($naic != ''){
                                    $naics_list .= "\n".'• '.$naic.'';
                                }
                                }

                            endif;
                            $naics = array();
                            else:
                            $naics_list = '-';
                            endif;

                            $description        = get_post_meta( get_the_ID(), 'mm365_company_description', true );
                            $contact_person     = get_post_meta( get_the_ID(), 'mm365_contact_person', true );
                            $contact_address    = get_post_meta( get_the_ID(), 'mm365_company_address', true );
                            $contact_phone      = get_post_meta( get_the_ID(), 'mm365_company_phone', true );
                            $contact_email      = get_post_meta( get_the_ID(), 'mm365_company_email', true );

                            $alt_phone          = get_post_meta( get_the_ID(), 'mm365_alt_phone', true );
                            $alt_email          = get_post_meta( get_the_ID(), 'mm365_alt_email', true );
                            $alt_contact        = get_post_meta( get_the_ID(), 'mm365_alt_contact_person', true );

                            $website            = get_post_meta( get_the_ID(), 'mm365_website', true );
                            $zip_code           = get_post_meta( get_the_ID(), 'mm365_zip_code', true );
                            $minority_category  = $this->expand_minoritycode(get_post_meta( get_the_ID(), 'mm365_minority_category', true ));

                            //Get Council Details
                            $cmp_council_id     = get_post_meta( get_the_ID(), 'mm365_company_council', true );
                            $council_name       = get_the_title($cmp_council_id);
                            $council_short_name = get_post_meta( $cmp_council_id, 'mm365_council_shortname', true );


                            //Type
                            $cmp_service_type = $this->get_company_service_type(get_post_meta( get_the_ID(), 'mm365_service_type', true ));

                            //Capability statements
                            $capability_statements = "";
                            $serviceable_locations = "-";
                            if($cmp_service_type == 'buyer' OR $cmp_service_type == 'Buyer' ): 
                                $capability_statements = "Not Applicable"; 
                                $serviceable_locations = "Not Applicable";
                            else: 
                                if(get_post_meta( get_the_ID(), 'mm365_company_docs', true ) !=''):
                                    foreach(get_post_meta( get_the_ID(), 'mm365_company_docs', true ) as $attachment_id => $attachment_url){
                                    $capability_statements .= '• '.basename(get_attached_file( $attachment_id ))."\n\n";                                            
                                    }
                                else:
                                    $capability_statements = "-";
                                endif;

                                if(!empty(get_post_meta( get_the_ID(), 'mm365_cmp_serviceable_countries')) ):
                                    $breaks = array("<br />","<br>","<br/>");  
                                    $countries = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_countries');
                                    $states    = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_states');
                                    $locations = str_ireplace($breaks, "\r\n", $this->multi_countries_state_display($countries, $states));  
                                    $serviceable_locations = $locations;
                                endif;
                            endif;

                            //Int assi looking for
                            if(!empty((get_post_meta( get_the_ID(), 'mm365_international_assistance' )))):
                                foreach ((get_post_meta( get_the_ID(), 'mm365_international_assistance')) as $key => $value){
                                  $looking_for[] = $value;
                                }

                                if(isset($looking_for)):
                                    //$minority_codes_list = implode( ', ', $mincode );
                                    $looking_for_list = '';
                                    foreach ($looking_for as $ldata) {
                                        $looking_for_list .= "\n".'• '.$ldata.'';
                                    }
                                endif;
                                $looking_for = array();
                            else:
                                $looking_for_list = '-';
                            endif;


                            if($website!=''){
                                if (!filter_var($website, FILTER_VALIDATE_URL)){
                                    $website = '=HYPERLINK("http://'.$website.'", "'.$website.'")';
                                }else{  $website = '=HYPERLINK("'.$website.'", "'.$website.'")';}
                            }

                            //Check if buyer
                            if(get_post_meta( get_the_ID(), 'mm365_service_type', true ) == 'seller' ){
                                $certified = ( get_post_meta(get_the_ID(), 'mm365_certification_status', true ) == 'verified') ? 'Yes' : 'No';
                            } else $certified = 'NA';

                            $company = array(
                                htmlspecialchars_decode(get_the_title(get_the_ID())),
                                $cmp_service_type,
                                $council_short_name,
                                $certified,
                                htmlspecialchars_decode(strip_tags($description)),
                                $services_list,
                                $serviceable_locations,
                                $minority_category,
                                $capability_statements,
                                $looking_for_list,
                                $industries_list,
                                ($website != '') ? $website:'-',  
                                $contact_person,
                                $contact_address ,
                                $contact_phone,
                                $contact_email ,
                                $city,
                                $state,
                                $country,
                                $zip_code,
                                ($alt_contact != '') ? $alt_contact  :'-', 
                                ($alt_phone != '') ? $alt_phone :'-', 
                                ($alt_email != '') ? $alt_email:'-', 
                                ($ec != '') ? $ec:'-', 
                                ($company_size != '') ? $company_size:'-', 
                                ($customers != '') ? $customers:'-', 
                                $certifications_list,
                                $naics_list,
                                get_post_time("m/d/Y h:i A"),
                                get_the_modified_time("m/d/Y h:i A"),
                            );

                            array_push($data,$company);
                           
                    }


                }

            }

    

            $writer = new XLSXWriter();

            $styles1 = array( 'font'=>'Arial','font-size'=>10,'font-style'=>'bold', 'fill'=>'#ffc00', 'color'=>'#000','halign'=>'center','valign'=>'center','height'=>50,'wrap_text'=>true);
            $styles2 = array( 'font'=>'Arial','font-size'=>10,'font-style'=>'bold', 'fill'=>'#356ab3', 'color'=>'#fff','halign'=>'center','valign'=>'center','height'=>30, 'wrap_text'=>true);
            $styles3 = array( 'border'=>'left,right,top,bottom','border-color'=>'#000','border-style'=>'thin','wrap_text'=>true,'valign'=>'top');
            $styles4 = array( ['border'=>'left,right,top,bottom','border-color'=>'#000','border-style'=>'thin','font'=>'Arial','font-size'=>10, 'height'=>30,'font-style'=>'bold', 'fill'=>'#856ab3', 'color'=>'#fff','halign'=>'left','valign'=>'top','wrap_text'=>true],
            ['border'=>'left,right,top,bottom','border-color'=>'#000','border-style'=>'thin','font'=>'Arial','font-size'=>10, 'fill'=>'#fff', 'color'=>'#000','halign'=>'left','valign'=>'top','wrap_text'=>true]);

            $writer->writeSheetHeader('Sheet1', array('1'=>'string','2'=>'string','3'=>'string','4'=>'string','5'=>'string','6'=>'string','7'=>'string','8'=>'string','9'=>'string','10'=>'string','11'=>'string','12'=>'string','13'=>'string','14'=>'string','15'=>'string','16'=>'string','17'=>'string','18'=>'string','19'=>'string','20'=>'string','21'=>'string','22'=>'string','23'=>'string','24'=>'string','25'=>'string','26'=>'string','27'=>'string','28'=>'string','29'=>'string','30'=>'string'),
            $col_options = ['widths'=>[30,15,15,15,45,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30],'suppress_row'=>true] );

            $writer->writeSheetRow('Sheet1',array( "Search Results for \n",""), $styles1 );          

            $writer->writeSheetRow('Sheet1', array("Keyword",$keyword), $styles4 );


            if(!empty($services)){
               $writer->writeSheetRow('Sheet1', array("Company products or services ", implode( ', ', $services)), $styles4 );
            }
            if($council != NULL){
              $writer->writeSheetRow('Sheet1', array("Council",$this->get_council_info($council)), $styles4 );
            }

            $writer->writeSheetRow('Sheet1', array(""), $styles3 );

            //Excel sheet column headings
            $writer->writeSheetRow('Sheet1', 
            $rowdata =  array(
                'Company name',
                'Service type',
                'Council',
                'Certified',
                'Description',
                'Company Services',
                'Locations where services or products are available',
                'Minority classification',
                'Capability statement',
                'International assistance from council',
                'Industry',
                'Website',
                'Contact person',
                'Address',         
                'Phone',
                'Email',
                'City',
                'State',
                'Country',
                'ZIP Code',
                'Alternate contact',
                'Alternate phone',
                'Alternate email',
                'Number of employees',
                'Size',
                'Current customers',
                'Industry Certifications',
                'NAICS codes',
                'Created date',
                'Updated date'), $styles2 );

    
            //Write XLS
            foreach($data as $dat){
                $writer->writeSheetRow('Sheet1', $dat,$styles3);
            }


            $file = 'Search & download Report - '.time().'.xlsx';
            $writer->writeToFile($file);

            if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                readfile($file);
                unlink($file);
                exit;
            }


            wp_reset_postdata();
            die();




    }




//Class ends here
}
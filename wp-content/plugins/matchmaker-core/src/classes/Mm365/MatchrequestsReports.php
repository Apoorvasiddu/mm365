<?php

namespace Mm365;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MatchrequestsReports
{
    use CertificateAddon;
    use CompaniesAddon;
    use CountryStateCity;
    use CouncilAddons;
    use ReusableMethods;
  

    function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'assets'), 11);

        add_action('wp_ajax_mm365_admin_viewreport_match_listing', array($this, 'quick_view'), 11);

        add_filter('mm365_admin_quickreports_matchrequests', array($this, 'quick_download'), 11, 3);

        add_action('wp_ajax_mm365_admin_viewreport_match_filtered_listing', array($this, 'filtered_view'), 11, 0);

        add_filter('mm365_admin_filteredreports_matchrequests', array($this, 'filtered_download'), 10, 1);

        add_filter('mm365_matchrequest_result_download', array($this, 'mm365_matchresults_download'), 10, 1);

        add_filter('mm365_matchrequest_submitted_requests_download',[$this,'mm365_matchrequests_download'],10,2);

    }

    function assets()
    {

        wp_register_script('mm365admin_reports', plugins_url('matchmaker-core/assets/admin_report.js'), array('jquery'));
        wp_enqueue_script('mm365admin_reports');

        $localize = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        );
        wp_register_script('admin_list_admin_viewreport_match', plugins_url('matchmaker-core/assets/admin_view_matchrequest_report.js'), array('jquery'), false, true);
        wp_localize_script('admin_list_admin_viewreport_match', 'adminViewReportMatchAjax', $localize);
        wp_enqueue_script('admin_list_admin_viewreport_match');

        wp_register_script('admin_list_admin_viewreport_match_filtered', plugins_url('matchmaker-core/assets/admin_view_matchrequest_report_filtered.js'), array('jquery'), false, true);
        wp_localize_script('admin_list_admin_viewreport_match_filtered', 'adminViewReportMatchFilteredAjax', $localize);
        wp_enqueue_script('admin_list_admin_viewreport_match_filtered');


    }

    /**
     * 
     * 
     */
    function quick_view()
    {

        $user = wp_get_current_user();

        $request = $_GET;
        $period = $_REQUEST['period'];
        $meta = $_REQUEST['meta'];
        $council_id = $this->get_userDC($user->ID);

        //If current user is not council manager, check if super admin is filterin view with specific council
        $is_admin_filtering = 'no';
        if ($_REQUEST['sa_council_filter'] != '' and $council_id == '') {
            $is_admin_filtering = 'yes';
        }

        //override council id by admin selected council id while filtering
        if ($council_id == '') {
            $council_id = $_REQUEST['sa_council_filter'];
        }

        //If admin is not filtering or selected all council, this will be skipped in council_id check below


        if ($meta != 'x') {
            $meta_additional = array(
                'key' => 'mm365_matchrequest_status',
                'value' => $meta,
                'compare' => '=',
            );
        } else
            $meta_additional = '';

        if ($council_id != '') {
            $council_filter = array(
                'key' => 'mm365_requester_company_council',
                'value' => $council_id,
                'compare' => '=',
            );
        } else
            $council_filter = '';

        $end = date('Y-m-d');
        $start = date('Y-m-d', strtotime("-1 $period"));


        if ($meta == 'closed'):
            $columns = array(
                0 => 'company_name',
                1 => 'services_or_products',
                2 => 'industry',
                3 => 'looking_for',
                4 => 'status',
                5 => 'reason_for_closure',
                6 => 'message',
                7 => 'matched_companies',
                8 => 'approved_by',
                9 => 'requested_date_and_time',
            );
        else:
            $columns = array(
                0 => 'company_name',
                1 => 'services_or_products',
                2 => 'industry',
                3 => 'looking_for',
                4 => 'status',
                5 => 'matched_companies',
                6 => 'approved_by',
                7 => 'requested_date_and_time',
            );
        endif;

        $args = array(
            'post_type' => 'mm365_matchrequests',
            'post_status' => 'publish',
            'posts_per_page' => $request['length'],
            'offset' => $request['start'],
            'order' => $request['order'][0]['dir'],
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'mm365_matched_companies_last_updated_isodate',
                    'value' => array($start, $end),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ),
                $meta_additional,
                $council_filter
            )
        );

        if ($request['order'][0]['column'] == 0) {
            //$args['orderby'] = $columns[$request['order'][0]['column']];
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_requester_company_name';

        } elseif ($request['order'][0]['column'] == 1) {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_matched_companies_last_updated';
        } elseif ($request['order'][0]['column'] == 2) {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_location_for_search';
        } elseif ($request['order'][0]['column'] == 3) {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_services_details';
        } elseif ($request['order'][0]['column'] == 4) {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_matchrequest_status';
        }
        //conditional column
        elseif ($request['order'][0]['column'] == 5 and $meta == 'closed') {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_reason_for_closure_filter';
        }

        if (!empty($request['search']['value'])) { // When datatables search is used
            $args['orderby'] = array('modified' => 'DESC');


            if ($request['order'][0]['column'] == 0) {
                //$args['orderby'] = $columns[$request['order'][0]['column']];
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_requester_company_name';

            } elseif ($request['order'][0]['column'] == 1) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_matched_companies_last_updated';
            } elseif ($request['order'][0]['column'] == 2) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_location_for_search';
            } elseif ($request['order'][0]['column'] == 3) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_services_details';
            } elseif ($request['order'][0]['column'] == 4) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_matchrequest_status';
            } elseif ($request['order'][0]['column'] == 6) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_matched_companies_last_updated';
            }
            //conditional column
            elseif ($request['order'][0]['column'] == 5 and $meta == 'closed') {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_reason_for_closure_filter';
            }

            //Search conditional columns
            if ($meta == 'closed') {
                $conditional_col_search = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mm365_reason_for_closure_filter',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_reason_for_closure',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                );
            } else {
                $conditional_col_search = NULL;
            }

            //Council ID condition to search
            if ($council_id == '') {
                $conditional_council_search = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mm365_requester_company_council',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    )
                );
            } else {
                $conditional_council_search = NULL;
            }

            //mm365_reason_for_closure
            $args['meta_query'] = array(

                'relation' => 'AND',
                $council_filter,
                array(
                    'key' => 'mm365_matched_companies_last_updated_isodate',
                    'value' => array($start, $end),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ),
                $meta_additional,
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mm365_services_details',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_requester_company_name',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_location_for_search',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_matchrequest_status',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_matched_companies_last_updated',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    $conditional_col_search,
                    $conditional_council_search
                )

            );
        }
        //print_r($args); die();
        $match_query = new \WP_Query($args);
        $totalData = $match_query->found_posts;

        if ($match_query->have_posts()) {
            while ($match_query->have_posts()) {
                $match_query->the_post();

                //services
                if (!empty((get_post_meta(get_the_ID(), 'mm365_services_looking_for')))):
                    foreach ((get_post_meta(get_the_ID(), 'mm365_services_looking_for')) as $key => $value) {
                        $services[] = $value;
                    }
                    if (isset($services)):
                        //$services_list =  implode( ', ', $services );
                        $services_list = '';
                        foreach ($services as $service) {
                            $services_list .= "<div class='intable_span'>" . $service . '</div>';
                        }
                    endif;
                    $services = array();
                else:
                    $services_list = '-';
                endif;

                //Industries
                if (!empty((get_post_meta(get_the_ID(), 'mm365_services_industry')))):
                    foreach ((get_post_meta(get_the_ID(), 'mm365_services_industry')) as $key => $value) {
                        $industries[] = $value;
                    }
                    if (isset($industries)):
                        //$industries_list =  implode( ', ', $industries );
                        $industries_list = '';
                        foreach ($industries as $industry) {
                            $industries_list .= "<div class='intable_span'>" . $industry . '</div>';
                        }
                    endif;
                    $industries = array();
                else:
                    $industries_list = '-';
                endif;

                //Matched companies
                if (get_post_meta(get_the_ID(), 'mm365_matched_companies', true) != ''):
                    foreach (maybe_unserialize(get_post_meta(get_the_ID(), 'mm365_matched_companies', true)) as $key => $value) {
                        if ($meta == 'approved') {
                            if ($value[1] == 1) {
                                $matched_companies[] = $value[0];
                            }
                        } else {
                            $matched_companies[] = $value[0];
                        }
                    }
                    if (isset($matched_companies)):
                        //$matched_companies_list =  implode( '•', $matched_companies );
                        $matched_companies_list = '<ol class="matched-companies-list">';
                        foreach ($matched_companies as $company) {
                            if ($company != '') {
                                $matched_companies_list .= '<li><a href="' . site_url() . '/view-company?cid=' . $company . '&mr_id=' . get_the_ID() . '">' . preg_replace("/&#?[a-z0-9]+;/i", " ", wp_filter_nohtml_kses(get_the_title($company))) . '</a></li>';
                            }
                        }
                        $matched_companies_list .= '</ol>';
                    endif;
                    $matched_companies = array();
                else:
                    $matched_companies_list = '-';
                endif;


                $status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);
                $company_id = get_post_meta(get_the_ID(), 'mm365_requester_company_id', true);
                $last_updated_byuser = get_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated', true);

                if ($company_id != ''):
                    $company_name = get_the_title($company_id);
                else:
                    $company_name = '';
                endif;

                $approver = get_post_meta(get_the_ID(), 'mm365_matched_companies_approved_by', true);
                if ($approver != '') {
                    $approver = get_userdata($approver)->user_login;
                }
                $details = get_post_meta(get_the_ID(), 'mm365_services_details', true);

                $reason_for_closure = get_post_meta(get_the_ID(), 'mm365_reason_for_closure_filter', true);
                $closure_message = get_post_meta(get_the_ID(), 'mm365_reason_for_closure', true);

                //Get Council Details
                $requester_council_id = get_post_meta(get_the_ID(), 'mm365_requester_company_council', true);
                $council_name = get_the_title($requester_council_id);
                $council_short_name = get_post_meta($requester_council_id, 'mm365_council_shortname', true);

                $nestedData = array();

                $nestedData[] = $this->get_certified_badge($company_id, true) . '<a href="' . site_url() . '/view-company?cid=' . $company_id . '">' . $company_name . '</a>';
                if ($council_id == '' or $is_admin_filtering == 'yes'):
                    $nestedData[] = $council_short_name;
                endif;
                $nestedData[] = $services_list;
                $nestedData[] = $industries_list;
                $nestedData[] = $details;
                switch ($status) {
                    case 'nomatch':
                        $nestedData[] = "<span class='" . $status . "'>No Match</span>";
                        break;
                    case 'auto-approved':
                        $nestedData[] = "<span class='" . $status . "'>Auto Approved</span>";
                        break;
                    default:
                        $nestedData[] = "<span class='" . $status . "'>" . ucfirst($status) . "</span>";
                        break;
                }
                if ($meta == 'completed' or $meta == 'cancelled'):
                    $nestedData[] = $reason_for_closure;
                    $nestedData[] = $closure_message;
                endif;
                $nestedData[] = $matched_companies_list;
                $nestedData[] = $approver ?: '-';
                $nestedData[] = $last_updated_byuser;
                $data[] = $nestedData;
            }

            wp_reset_query();
            header("Content-Type: application/json");

            $json_data = array(
                "draw" => intval($request['draw']),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalData),
                "data" => $data
            );
            echo json_encode($json_data);

        } else {
            $json_data = array(
                "data" => array()
            );
            echo json_encode($json_data);
        }
        wp_die();


    }

    /**
     * 
     *
     */
    function quick_download($period = 'week', $match_status = NULL, $sa_council_filter = NULL)
    {
        //IF sa_council_filter is present ovveride councilid
        if ($sa_council_filter != NULL) {
            $councilid = $sa_council_filter;
        } else
            $councilid = NULL;

        if ($councilid != '') {

            $council_col_width = 0;

            //Match requests
            $council_filter_matchrequests = array(
                'key' => 'mm365_requester_company_council',
                'value' => $councilid,
                'compare' => '=',
            );


            $council_shortname = $this->get_council_info($councilid) . " - ";
        } else {

            $council_filter_matchrequests = '';
            $council_col_width = 30;
            $council_shortname = '';
        }



        $end = date('Y-m-d');
        $start = date('Y-m-d', strtotime("-1 $period"));

        switch ($match_status) {
            case 'approved':
                $quickreports_match_args = array(
                    'posts_per_page' => -1,
                    // No limit
                    'post_type' => 'mm365_matchrequests',
                    'post_status' => array('publish'),
                    'meta_query' => array(
                        array(
                            'key' => 'mm365_matched_companies_last_updated_isodate',
                            'value' => array($start, $end),
                            'compare' => 'BETWEEN',
                            'type' => 'DATE'
                        ),
                        array(
                            'key' => 'mm365_matchrequest_status',
                            'value' => 'approved',
                            'compare' => '=',
                        ),
                        $council_filter_matchrequests
                    )

                );
                $file_name = "Report - " . $council_shortname . "Approved Match Requests with in a " . $period;
                $hidden_column_30 = 0;
                $hidden_column_50 = 0;
                $reason_for_label = '';
                break;
            case 'auto-approved':
                $quickreports_match_args = array(
                    'posts_per_page' => -1,
                    // No limit
                    'post_type' => 'mm365_matchrequests',
                    'post_status' => array('publish'),
                    'meta_query' => array(
                        array(
                            'key' => 'mm365_matched_companies_last_updated_isodate',
                            'value' => array($start, $end),
                            'compare' => 'BETWEEN',
                            'type' => 'DATE'
                        ),
                        array(
                            'key' => 'mm365_matchrequest_status',
                            'value' => 'auto-approved',
                            'compare' => '=',
                        ),
                        $council_filter_matchrequests
                    )

                );
                $file_name = "Report - " . $council_shortname . "Auto Approved Match Requests with in a " . $period;
                $hidden_column_30 = 0;
                $hidden_column_50 = 0;
                $reason_for_label = '';
                break;

            case 'completed':
                $quickreports_match_args = array(
                    'posts_per_page' => -1,
                    // No limit
                    'post_type' => 'mm365_matchrequests',
                    'post_status' => array('publish'),
                    'meta_query' => array(
                        array(
                            'key' => 'mm365_matched_companies_last_updated_isodate',
                            'value' => array($start, $end),
                            'compare' => 'BETWEEN',
                            'type' => 'DATE'
                        ),
                        array(
                            'key' => 'mm365_matchrequest_status',
                            'value' => 'completed',
                            'compare' => '=',
                        ),
                        $council_filter_matchrequests
                    )

                );
                $file_name = "Report - " . $council_shortname . "Completed Match Requests with in a " . $period;
                $hidden_column_30 = 30;
                $hidden_column_50 = 30;
                $reason_for_label = 'completion';
                break;

            case 'cancelled':
                $quickreports_match_args = array(
                    'posts_per_page' => -1,
                    // No limit
                    'post_type' => 'mm365_matchrequests',
                    'post_status' => array('publish'),
                    'meta_query' => array(
                        array(
                            'key' => 'mm365_matched_companies_last_updated_isodate',
                            'value' => array($start, $end),
                            'compare' => 'BETWEEN',
                            'type' => 'DATE'
                        ),
                        array(
                            'key' => 'mm365_matchrequest_status',
                            'value' => 'cancelled',
                            'compare' => '=',
                        ),
                        $council_filter_matchrequests
                    )

                );
                $reason_for_label = 'cancellation';
                $file_name = "Report - " . $council_shortname . "Cancelled Match Requests with in a " . $period;
                $hidden_column_30 = 30;
                $hidden_column_50 = 30;
                break;

            default:
                $end = date('Y-m-d');
                $start = date('Y-m-d', strtotime("-1 $period"));
                $quickreports_match_args = array(
                    'posts_per_page' => -1,
                    // No limit
                    'post_type' => 'mm365_matchrequests',
                    'post_status' => array('publish'),
                    'meta_query' => array(
                        array(
                            'key' => 'mm365_matched_companies_last_updated_isodate',
                            'value' => array($start, $end),
                            'compare' => 'BETWEEN',
                            'type' => 'DATE'
                        ),
                        $council_filter_matchrequests
                    )
                );
                $file_name = "Report - " . $council_shortname . "Match Requests with in a " . $period;
                $hidden_column_30 = 0;
                $hidden_column_50 = 0;
                $reason_for_label = '';
                break;
        }



        $data = array();


        $report_query = new \WP_Query($quickreports_match_args);

        while ($report_query->have_posts()):
            $report_query->the_post();


            //services
            if (!empty((get_post_meta(get_the_ID(), 'mm365_services_looking_for')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_services_looking_for')) as $key => $value) {
                    $services[] = $value;
                }
                if (isset($services)):
                    //$services_list =  implode( ', ', $services );
                    $services_list = '';
                    foreach ($services as $service) {
                        $services_list .= "\n" . '• ' . $service . '';
                    }

                endif;
                $services = array();
            else:
                $services_list = '-';
            endif;

            //Industries
            if (!empty((get_post_meta(get_the_ID(), 'mm365_services_industry')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_services_industry')) as $key => $value) {
                    $industries[] = $value;
                }
                if (isset($industries)):
                    //$industries_list =  implode( ', ', $industries );
                    $industries_list = '';
                    foreach ($industries as $industry) {
                        $industries_list .= "\n" . '• ' . $industry . '';
                    }
                endif;
                $industries = array();
            else:
                $industries_list = '-';
            endif;



            //Location
            $location_searching = get_post_meta(get_the_ID(), 'mm365_location_for_search', true);
            $breaks = array("<br />", "<br>", "<br/>");
            $service_required_locations = str_ireplace($breaks, "\r\n", $location_searching);

            $country_id = get_post_meta(get_the_ID(), 'mm365_service_country', true);
            if (is_numeric($country_id)):
                $country = $this->get_countryname($country_id);
            else:
                $country = 'Any Country';
            endif;


            $states = get_post_meta(get_the_ID(), 'mm365_service_state');
            $state_names = array();
            foreach ($states as $state_id) {
                if (is_numeric($state_id)) {
                    array_push($state_names, $this->get_statename($state_id));
                }
            }
            if (!empty($state_names))
                $state = implode(", ", $state_names);
            if (empty($states) or $states[0] == 'all')
                $state = "Any State";

            $city_ids = get_post_meta(get_the_ID(), 'mm365_service_city');
            $city_names = array();
            foreach ($city_ids as $city_id) {
                if (is_numeric($city_id)) {
                    array_push($city_names, $this->get_cityname($city_id));
                }
            }
            if (!empty($city_names))
                $city = implode(", ", $city_names);
            if (empty($city_ids) or $city_ids[0] == 'all')
                $city = "Any city";


            //Company size
            $employee_count = get_post_meta(get_the_ID(), 'mm365_number_of_employees', true);
            if ($employee_count == '&lt; 20'):
                $ec = "< 20";
            else:
                $ec = $employee_count;
            endif;
            $size = get_post_meta(get_the_ID(), 'mm365_size_of_company', true);
            if ($size == '&lt;$100,000'):
                $company_size = "< $100,000";
            else:
                $company_size = $size;
            endif;

            //Certification
            $ar_certification = (get_post_meta(get_the_ID(), 'mm365_certifications'));
            if (!empty($ar_certification)) {
                $certifications = array();
                foreach ($ar_certification as $key => $value) {
                    $certifications[] = $value;
                }
                if (isset($certifications)):
                    //$certifications_list =  implode( ', ', $certifications );
                    $certifications_list = '';
                    foreach ($certifications as $certificate) {
                        $certifications_list .= "\n" . '• ' . $certificate . '';
                    }
                endif;
                $certifications = array();
            } else {
                $certifications_list = '-';
            }

            //NAICS Codes
            if (!empty((get_post_meta(get_the_ID(), 'mm365_naics_codes')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_naics_codes')) as $key => $value) {
                    $naics[] = $value;
                }
                if (isset($naics)):
                    //$naics_list = implode( ', ', $naics );
                    $naics_list = '';
                    foreach ($naics as $naic) {
                        if ($naic != '') {
                            $naics_list .= "\n" . '• ' . $naic . '';
                        }
                    }

                endif;
                $naics = array();
            else:
                $naics_list = '-';
            endif;


            //array('Requester Company','Services','Industry','City','State','Country','Number of employees','Size of company','Certifications','NAICS Codes','Match Status','Matched Companies','Approved by','Time of approval'),
            $matched_companies_list = '-';
            if (get_post_meta(get_the_ID(), 'mm365_matched_companies', true) != ''):
                foreach (maybe_unserialize(get_post_meta(get_the_ID(), 'mm365_matched_companies', true)) as $key => $value) {
                    if ($match_status == 'approved') {
                        if ($value[1] == 1) {
                            $matched_companies[] = $this->replace_html_in_companyname(get_the_title($value[0]));
                        }
                    } else {
                        $matched_companies[] = $this->replace_html_in_companyname(get_the_title($value[0]));
                    }
                }
                if (!empty($matched_companies)):
                    $matched_companies_list = '';
                    foreach ($matched_companies as $company) {
                        if ($company != '') {
                            $matched_companies_list .= "\n" . '• ' . $company . '';
                        }
                    }
                endif;
                $matched_companies = array();
            else:
                $matched_companies_list = '-';
            endif;

            //Minority Codes List
            if (!empty((get_post_meta(get_the_ID(), 'mm365_mr_mbe_category')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_mr_mbe_category')) as $key => $value) {
                    $mincode[] = $this->expand_minoritycode($value);
                }
                if (isset($mincode)):
                    $minority_codes_list = '';
                    foreach ($mincode as $minority) {
                        $minority_codes_list .= "\n" . '• ' . $minority . '';
                    }

                endif;
                $mincode = array();
            else:
                $minority_codes_list = '-';
            endif;


            $status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);
            switch ($status) {
                case 'auto-approved':
                    $status = 'Auto Approved';
                    break;
                case 'nomatch':
                    $status = 'No Match';
                    break;
                default:
                    $status = ucfirst($status);
                    break;
            }

            //Int assi looking for
            if (!empty((get_post_meta(get_the_ID(), 'mm365_match_intassi_lookingfor')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_match_intassi_lookingfor')) as $key => $value) {
                    $looking_for[] = $value;
                }
                if (isset($looking_for)):
                    $looking_for_list = '';
                    foreach ($looking_for as $ldata) {
                        $looking_for_list .= "\n" . '• ' . $ldata . '';
                    }

                endif;
                $looking_for = array();
            else:
                $looking_for_list = '-';
            endif;


            //Get Council Details
            $requester_council_id = get_post_meta(get_the_ID(), 'mm365_requester_company_council', true);
            $council_name = get_the_title($requester_council_id);
            $council_short_name = get_post_meta($requester_council_id, 'mm365_council_shortname', true);
            $minority_code = $this->expand_minoritycode(get_post_meta(get_the_ID(), 'mm365_minority_category', true));

            $approver = get_post_meta(get_the_ID(), 'mm365_matched_companies_approved_by', true);
            $approved_time = get_post_meta(get_the_ID(), 'mm365_matched_companies_approved_time', true);
            $details = get_post_meta(get_the_ID(), 'mm365_services_details', true);
            $created_time = get_post_time("m/d/Y h:i A");
            $modified_time = get_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated', true);

            $reason_for_closure = get_post_meta(get_the_ID(), 'mm365_reason_for_closure_filter', true);
            $closure_message = get_post_meta(get_the_ID(), 'mm365_reason_for_closure', true);

            if ($approver != '') {
                $approver = get_userdata($approver)->user_login;
            }

            if ($approved_time != '') {
                $approved_time = wp_date('m/d/Y g:i A', $approved_time);
            }
            $requester_cmp_id = get_post_meta(get_the_ID(), 'mm365_requester_company_id', true);
            $requester_company = get_the_title($requester_cmp_id);

            //Check if buyer
            if (get_post_meta($requester_cmp_id, 'mm365_service_type', true) == 'seller') {
                $certified = (get_post_meta($requester_cmp_id, 'mm365_certification_status', true) == 'verified') ? 'Yes' : 'No';
            } else
                $certified = 'NA';


            $matchrequest = array(
                $this->replace_html_in_companyname($requester_company),
                $council_short_name,
                $certified,
                $services_list,
                $industries_list,
                $minority_codes_list,
                $details,
                $service_required_locations,
                ($ec ?: '-'),
                ($company_size ?: '-'),
                $certifications_list,
                $naics_list,
                $looking_for_list,
                $status,
                $reason_for_closure ?: '-',
                $closure_message ?: '-',
                $matched_companies_list,
                ($approver ?: '-'),
                $created_time,
                $modified_time,
                ($approved_time ?: '-')
            );
            array_push($data, $matchrequest);
        endwhile;


        $writer_2 = new XLSXWriter();

        $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 50, 'wrap_text' => true);
        $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 20);
        $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');

        $writer_2->writeSheetHeader(
            'Sheet1',
            array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string', '9' => 'string', '10' => 'string', '11' => 'string', '12' => 'string', '13' => 'string', '14' => 'string', '15' => 'string', '16' => 'string', '17' => 'string', '18' => 'string', '19' => 'string'),
            $col_options = [
                'widths' => [30, $council_col_width, 20, 40, 30, 50, 30, 30, 30, 30, 30, 30, 30, $hidden_column_30, $hidden_column_50, 30, 30, 30, 30, 30],
                'suppress_row' => true
            ]
        );
        if ($councilid == ''):
            $writer_2->writeSheetRow('Sheet1', $rowdata = array($file_name, 'From ' . date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))) . "-1 " . $period)) . ' To ' . date('m/d/Y', time())), $styles1);
        else:
            $writer_2->writeSheetRow('Sheet1', $rowdata = array($file_name . ' From ' . date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))) . "-1 " . $period)) . ' To ' . date('m/d/Y', time())), $styles1);
        endif;
        $writer_2->writeSheetRow(
            'Sheet1',
            $rowdata = array(
                'Requester company',
                'Requester council',
                'Certified',
                'Services or products',
                'Industry',
                'Minority classifications',
                'Request details',
                'Location where products or services are required',
                'Number of employees',
                'Size of company',
                'Industry Certifications',
                'NAICS codes',
                'Looking for international assistance',
                'Match status',
                'Reason for ' . $reason_for_label,
                'Message',
                'Matched companies',
                'Approved by',
                'Created date',
                'Modified date',
                'Time of approval'
            ),
            $styles2
        );

        foreach ($data as $dat) {
            $writer_2->writeSheetRow('Sheet1', $dat, $styles3);
        }
        //$writer_2->writeSheet($data);

        $file_2 = $file_name . '.xlsx';
        $writer_2->writeToFile($file_2);

        if (file_exists($file_2)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_2) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_2));
            readfile($file_2);
            unlink($file_2);
            exit;
        }


    }

    /**
     * 
     * 
     * 
     */
    function filtered_view()
    {

        /**
         * This same functions handles report for super_admin, super_buyer
         * and council_manager roles
         * 
         * 
         */
        $user = wp_get_current_user();

        //Check if council manager is reading the reports
        $acessing_council_id = $this->get_userDC($user->ID);

        $request = $_GET;

        //Parameters
        $from_date_us = $request['from_date'];
        $to_date_us = $request['to_date'];

        $from_date = date("Y-m-d", strtotime($from_date_us));
        $to_date = date("Y-m-d", strtotime($to_date_us));

        $match_status = $request['match_status'];
        $match_closure_filter = $request['closure_filter'];

        $search_employees = $request['number_of_employees'];
        $search_companysize = $request['size_of_company'];
        $minority_code = $request['minority_category'];
        $naics_codes = $request['naics_codes'];
        $search_council = $request['council'];



        if ($match_status != 'all' and !empty($match_status)) {
            $match_status_search = array('key' => 'mm365_matchrequest_status', 'value' => $match_status, 'compare' => '=');
        } else {
            $match_status_search = '';
        }



        $ec_search_params = array(
            "20 to 50",
            "50 to 100",
            "100 to 200",
            "200 to 500",
            "500 to 1000",
            "1000+"
        );
        if ($search_employees != NULL and !in_array($search_employees, $ec_search_params)):
            $ec_search = $ec_search_params;
            $ec_compare = "NOT IN";
        else:
            $ec_search = $search_employees;
            $ec_compare = "=";
        endif;

        $cs_search_params = array(
            "$100,000 - $500,000",
            "$500,000 - $1M",
            "$1M - $5M",
            "$5M - $50M",
            "$50M - $200M",
            "$200M - $500M",
            "$500M - $1B",
            "$1B+"
        );

        if ($search_companysize != NULL and !in_array($search_companysize, $cs_search_params)):
            $cs_search = $cs_search_params;
            $cs_compare = "NOT IN";
        else:
            $cs_search = $search_companysize;
            $cs_compare = "=";
        endif;


        $serv_array = array();
        $services = array();
        if (isset($request['services'])) {
            $services = array_filter($request['services'], array($this, 'purge_empty'));
            if (in_array('other', $services))
                array_push($services, $request['other_services']);
        }
        if (!empty($services)) {
            $serv_array = array(
                'key' => 'mm365_services_looking_for',
                'value' => $services,
                'compare' => 'IN',
            );
        }


        $indus_array = array();
        $indstry = array();
        if (isset($request['industry'])) {
            $indstry = array_filter($request['industry'], array($this, 'purge_empty'));
            if (in_array('other', $indstry))
                array_push($indstry, $request['other_industry']);
        }
        if (!empty($indstry)) {
            $indus_array = array(
                'key' => 'mm365_services_industry',
                'value' => $indstry,
                'compare' => 'IN',
            );
        }


        $certification_array = array();
        $certification = array();
        if (isset($request['certifications'])) {
            $certification = array_filter($request['certifications'], array($this, 'purge_empty'));
            if (in_array('other', $certification))
                array_push($certification, $request['other_certification']);
        }
        if (!empty($certification)) {
            $certification_array = array(
                'key' => 'mm365_certifications',
                'value' => $certification,
                'compare' => 'IN',
            );
        }



        $naics_array = array();
        $naics = array();
        if (isset($request['naics_codes'])) {
            $naics = array_filter($request['naics_codes'], array($this, 'purge_empty'));
        }
        $naics_to_search = array_filter($naics, 'strlen');
        if (!empty($naics_to_search)) {
            $naics_array = array(
                'key' => 'mm365_naics_codes',
                'value' => $naics_to_search,
                'compare' => 'IN',
            );
        }

        //International assitance from MMSDC
        $int_assi_array = array();
        $intassi = array_filter($_REQUEST['international_assistance'], array($this, 'purge_empty'));
        if (!empty($intassi)) {
            $int_assi_array = array(
                'key' => 'mm365_match_intassi_lookingfor',
                'value' => $intassi,
                'compare' => 'IN',
            );
        }


        //Countries and states where the service is required
        if (!empty($_GET['service_required_countries'])):
            $services_required_countries = $_GET['service_required_countries'];
        else:
            $services_required_countries = NULL;
        endif;

        if (!empty($_GET['service_required_states'])):
            $services_required_states = $_GET['service_required_states'];
        else:
            $services_required_states = NULL;
        endif;


        if (!empty($services_required_countries)) {
            $sl_countries_match = array(
                'key' => 'mm365_service_needed_country',
                'value' => $services_required_countries,
                'compare' => 'IN'
            );
        } else
            $sl_countries_match = NULL;

        if (!empty($services_required_states)) {
            $sl_states_match = array(
                'key' => 'mm365_service_needed_state',
                'value' => $services_required_states,
                'compare' => 'IN'
            );
        } else
            $sl_states_match = NULL;

        if ($minority_code != 'all' and !empty($minority_code)) {
            $minority_code_match = array('key' => 'mm365_mr_mbe_category', 'value' => $minority_code, 'compare' => '=');
        } else {
            $minority_code_match = array();
        }

        //Employee count
        if ($ec_search != '' or !empty($ec_search)) {

            if ($ec_compare == 'NOT IN') {
                $employee_match =
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => 'mm365_number_of_employees',
                            'value' => NULL,
                            'compare' => '!='
                        ),
                        array(
                            'key' => 'mm365_number_of_employees',
                            'value' => $ec_search,
                            'compare' => $ec_compare
                        )

                    );
            } else {
                $employee_match = array(
                    'key' => 'mm365_number_of_employees',
                    'value' => $ec_search,
                    'compare' => $ec_compare
                );

            }

        } else {
            $employee_match = array();
        }

        //Company size - sales size
        if ($cs_search != '' or !empty($cs_search)) {

            if ($cs_compare == 'NOT IN') {
                $companysize_match =
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => 'mm365_size_of_company',
                            'value' => NULL,
                            'compare' => '!='
                        ),
                        array(
                            'key' => 'mm365_size_of_company',
                            'value' => $cs_search,
                            'compare' => $cs_compare
                        )

                    );
            } else {
                $companysize_match =
                    array(
                        'key' => 'mm365_size_of_company',
                        'value' => $cs_search,
                        'compare' => $cs_compare
                    );
            }


        } else {
            $companysize_match = array();
        }


        //Search  filters for closure 
        if (isset($match_closure_filter) and $match_closure_filter != '') {
            $closure_reason_filter =
                array(
                    'key' => 'mm365_reason_for_closure_filter',
                    'value' => $match_closure_filter,
                    'compare' => '=',
                );

        } else {
            $closure_reason_filter = NULL;
        }

        //Council filtering

        $associated_buyers = get_user_meta($user->ID, '_mm365_associated_buyer'); //Used for super buyers 

        if (!empty($search_council) and $search_council != 'undefined') {
            $search_council_para = array(
                'key' => 'mm365_requester_company_council',
                'value' => $search_council,
                'compare' => '=',
            );
        } else {
            $search_council_para = array();
        }




        /**
         * Super Buyer Filter
         * This is applicable only for super buyer report
         * It can either be an array of super buyers captured from current user's metas
         * or shall be selected company from the 'My Buyer Team' drop down of super buyer report
         * 
         * 
         * Check if super buyer is filter 
         */

        $team_member = $request['buyer_team'] ?? NULL; //Can be 'all' or a specific ID only for super buyer

        if ($team_member != NULL) {


            if ($team_member != 'all') {

                $super_buyer_filter = array(
                    'key' => 'mm365_requester_company_id',
                    'value' => $team_member,
                    'compare' => '=',
                );

            } else {

                $super_buyer_filter = array(
                    'key' => 'mm365_requester_company_id',
                    'value' => $associated_buyers,
                    'compare' => 'IN',
                );

            }

        } else {
            $super_buyer_filter = array();
        }

        $args = array(
            'post_type' => 'mm365_matchrequests',
            'post_status' => 'publish',
            'posts_per_page' => $request['length'],
            'offset' => $request['start'],
            'order' => $request['order'][0]['dir'],
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'mm365_matched_companies_last_updated_isodate',
                    'value' => array($from_date, $to_date),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ),

                (array) $match_status_search,
                (array) $closure_reason_filter,
                $serv_array,
                $indus_array,
                $minority_code_match,
                (array) $sl_countries_match,
                (array) $sl_states_match,
                $naics_array,
                $certification_array,
                $employee_match,
                $companysize_match,
                $int_assi_array,
                $search_council_para,
                $super_buyer_filter
            )
        );



        if ($request['order'][0]['column'] == 0) {
            //$args['orderby'] = $columns[$request['order'][0]['column']];
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_requester_company_name';

        } elseif ($request['order'][0]['column'] == 1) {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_matched_companies_last_updated';
        } elseif ($request['order'][0]['column'] == 2) {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_location_for_search';
        } elseif ($request['order'][0]['column'] == 3) {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_services_details';
        } elseif ($request['order'][0]['column'] == 4) {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_matchrequest_status';
        }
        //conditional column
        elseif ($request['order'][0]['column'] == 5) {
            $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
            $args['meta_key'] = 'mm365_reason_for_closure_filter';
        }



        //Search conditional column
        if ($match_status == 'closed') {
            $conditional_col_search = array(
                'relation' => 'OR',
                array(
                    'key' => 'mm365_reason_for_closure_filter',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_reason_for_closure',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
            );
        } else {
            $conditional_col_search = NULL;
        }


        /**
         * Text searching - wild card
         * 
         * 
         */
        if (!empty($request['search']['value'])) { // When datatables search is used
            $args['orderby'] = array('modified' => 'DESC');


            if ($request['order'][0]['column'] == 0) {
                //$args['orderby'] = $columns[$request['order'][0]['column']];
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_requester_company_name';

            } elseif ($request['order'][0]['column'] == 1) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_matched_companies_last_updated';
            } elseif ($request['order'][0]['column'] == 2) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_location_for_search';
            } elseif ($request['order'][0]['column'] == 3) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_services_details';
            } elseif ($request['order'][0]['column'] == 4) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_matchrequest_status';
            } elseif ($request['order'][0]['column'] == 6) {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_matched_companies_last_updated';
            }
            //conditional column
            elseif ($request['order'][0]['column'] == 5 and $match_status == 'closed') {
                $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
                $args['meta_key'] = 'mm365_reason_for_closure_filter';
            }

            $args['meta_query'] = array(
                array(
                    'key' => 'mm365_matched_companies_last_updated_isodate',
                    'value' => array($from_date, $to_date),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ),
                $super_buyer_filter,
                $search_council_para,
                $match_status_search,
                $serv_array,
                $indus_array,
                $minority_code_match,
                $sl_countries_match,
                $sl_states_match,
                $naics_array,
                $certification_array,
                $employee_match,
                $companysize_match,
                $int_assi_array,
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mm365_services_details',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_requester_company_name',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_location_for_search',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_matchrequest_status',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_matched_companies_last_updated',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_requester_company_council',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => '='
                    ),
                    array(
                        'key' => 'mm365_company_council',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_services_looking_for',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'mm365_services_industry',
                        'value' => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    $conditional_col_search
                )

            );
        }
        //print_r($args); die();
        $match_query = new \WP_Query($args);
        $totalData = $match_query->found_posts;

        //echo $totalData.'<br/>'; print_r($args); die();
        header("Content-Type: application/json");
        if ($match_query->have_posts()) {
            while ($match_query->have_posts()) {
                $match_query->the_post();

                //services
                if (!empty((get_post_meta(get_the_ID(), 'mm365_services_looking_for')))):
                    foreach ((get_post_meta(get_the_ID(), 'mm365_services_looking_for')) as $key => $value) {
                        $services[] = $value;
                    }
                    if (isset($services)):
                        //$services_list =  implode( ', ', $services );
                        $services_list = '';
                        foreach ($services as $service) {
                            $services_list .= "<div class='intable_span'>" . $service . '</div>';
                        }
                    endif;
                    $services = array();
                else:
                    $services_list = '-';
                endif;

                //Industries
                if (!empty((get_post_meta(get_the_ID(), 'mm365_services_industry')))):
                    foreach ((get_post_meta(get_the_ID(), 'mm365_services_industry')) as $key => $value) {
                        $industries[] = $value;
                    }
                    if (isset($industries)):
                        //$industries_list =  implode( ', ', $industries );
                        $industries_list = '';
                        foreach ($industries as $industry) {
                            $industries_list .= "<div class='intable_span'>" . $industry . '</div>';
                        }
                    endif;
                    $industries = array();
                else:
                    $industries_list = '-';
                endif;

                //Matched companies
                if (get_post_meta(get_the_ID(), 'mm365_matched_companies', true) != ''):
                    foreach (maybe_unserialize(get_post_meta(get_the_ID(), 'mm365_matched_companies', true)) as $key => $value) {
                        if ($meta == 'approved') {
                            if ($value[1] == 1) {
                                $matched_companies[] = $value[0];
                            }
                        } else {
                            $matched_companies[] = $value[0];
                        }
                    }
                    if (isset($matched_companies)):
                        //$matched_companies_list =  implode( '•', $matched_companies );
                        $matched_companies_list = '<ol class="matched-companies-list">';
                        foreach ($matched_companies as $company) {
                            if ($company != '') {
                                $matched_companies_list .= '<li><a href="' . site_url() . '/view-company?cid=' . $company . '&mr_id=' . get_the_ID() . '">' . preg_replace("/&#?[a-z0-9]+;/i", " ", wp_filter_nohtml_kses(get_the_title($company))) . '</a></li>';
                            }
                        }
                        $matched_companies_list .= '</ol>';
                    endif;
                    $matched_companies = array();
                else:
                    $matched_companies_list = '-';
                endif;


                $status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);
                $company_id = get_post_meta(get_the_ID(), 'mm365_requester_company_id', true);
                $last_updated_byuser = get_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated', true);

                if ($company_id != ''):
                    $company_name = get_the_title($company_id);
                else:
                    $company_name = '';
                endif;
                $approver = get_post_meta(get_the_ID(), 'mm365_matched_companies_approved_by', true);
                if ($approver != '') {
                    $approver = get_userdata($approver)->user_login;
                }
                $details = get_post_meta(get_the_ID(), 'mm365_services_details', true);

                $reason_for_closure = get_post_meta(get_the_ID(), 'mm365_reason_for_closure_filter', true);
                $closure_message = get_post_meta(get_the_ID(), 'mm365_reason_for_closure', true);

                //Get Council Details
                $council_id = get_post_meta(get_the_ID(), 'mm365_requester_company_council', true);
                $council_name = get_the_title($council_id);
                $council_short_name = get_post_meta($council_id, 'mm365_council_shortname', true);

                $nestedData = array();

                $nestedData[] = $this->get_certified_badge($company_id, true) . '<a href="' . site_url() . '/view-company?cid=' . $company_id . '">' . $company_name . '</a>';
                if ($acessing_council_id == ''):
                    $nestedData[] = $council_short_name;
                endif;
                $nestedData[] = $services_list;
                $nestedData[] = $industries_list;
                $nestedData[] = $details;
                switch ($status) {
                    case 'nomatch':
                        $nestedData[] = "<span class='" . $status . "'>No Match</span>";
                        break;
                    case 'auto-approved':
                        $nestedData[] = "<span class='" . $status . "'>Auto Approved</span>";
                        break;
                    default:
                        $nestedData[] = "<span class='" . $status . "'>" . ucfirst($status) . "</span>";
                        break;
                }
                $nestedData[] = $reason_for_closure;
                $nestedData[] = $closure_message;
                $nestedData[] = $matched_companies_list;
                $nestedData[] = ($approver ?: '-');
                $nestedData[] = $last_updated_byuser;
                $data[] = $nestedData;
            }

            wp_reset_query();

            $json_data = array(
                "draw" => intval($request['draw']),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalData),
                "data" => $data
            );

            echo json_encode($json_data);

        } else {
            $json_data = array(
                "data" => array()
            );
            echo json_encode($json_data);
        }
        wp_die();

    }

    /**
     * 
     * 
     */
    function filtered_download($redirect_slug = 'reports-match-requests')
    {
        //check if viewer belongs to a council
        $user = wp_get_current_user();
        $council_id = $this->get_userDC($user->ID);

        if ($council_id != '') {
            $counil_col_width = 0;
        } else {
            $counil_col_width = 30;
        }

        //Parameters
        $from_date_us = $_POST['from_date'];
        $to_date_us = $_POST['to_date'];

        $from_date = date("Y-m-d", strtotime($from_date_us));
        $to_date = date("Y-m-d", strtotime($to_date_us));

        $match_status = $_POST['match_status'];
        $match_closure_filter = $_POST['match_closure_filter'];
        $search_employees = $_POST['number_of_employees'];
        $search_companysize = $_POST['size_of_company'];
        $minority_code = $_POST['minority_category'];
        $naics_codes = $_POST['naics_codes'];
        $search_council = $_POST['council_filter'] ?? NULL;

        //2.9 Onwards - Filter to specific requester company id / group of company ids for super user
        $team_member = $_POST['my_team_member'] ?? NULL;
        if (isset($team_member)) {

            $associated_buyers = array_values($_POST['associated_buyers_list']);
            if ($team_member != 'all') {
                $super_buyer_filter = array(
                    'key' => 'mm365_requester_company_id',
                    'value' => $team_member,
                    'compare' => '=',
                );
            } else {
                $super_buyer_filter = array(
                    'key' => 'mm365_requester_company_id',
                    'value' => $associated_buyers,
                    'compare' => 'IN',
                );
            }

        } else {
            $super_buyer_filter = array();
        }

        //Countries and states where the service is required
        if (isset($_POST['service_required_countries'])):
            $services_required_countries = $_POST['service_required_countries'];
        else:
            $services_required_countries = NULL;
        endif;

        if (isset($_POST['service_required_states'])):
            $services_required_states = $_POST['service_required_states'];
        else:
            $services_required_states = NULL;
        endif;

        //match request status
        if ($match_status != 'all' and !empty($match_status)) {
            $match_status_search = array('key' => 'mm365_matchrequest_status', 'value' => $match_status, 'compare' => '=');
        } else {
            $match_status_search = array();
        }

        //Search  filters for closure 
        if (isset($match_closure_filter) and $match_closure_filter != '') {
            $closure_reason_filter = array(
                'key' => 'mm365_reason_for_closure_filter',
                'value' => $match_closure_filter,
                'compare' => '=',
            );

        } else {
            $closure_reason_filter = NULL;
        }

        if ($search_employees == '< 20'):
            $ec_search = array(
                "20 to 50",
                "50 to 100",
                "100 to 200",
                "200 to 500",
                "500 to 1000",
                "1000+"
            );
            $ec_compare = "NOT IN";
        else:
            $ec_search = $search_employees;
            $ec_compare = "=";
        endif;

        $cs_search_params = array(
            "$100,000 - $500,000",
            "$500,000 - $1M",
            "$1M - $5M",
            "$5M - $50M",
            "$50M - $200M",
            "$200M - $500M",
            "$500M - $1B",
            "$1B+"
        );

        if ($search_companysize != NULL and !in_array($search_companysize, $cs_search_params)):
            $cs_search = $cs_search_params;
            $cs_compare = "NOT IN";
        else:
            $cs_search = $search_companysize;
            $cs_compare = "=";
        endif;

        //Services
        $serv_array = array();
        $services = array();
        if (isset($_POST['services'])) {
            $services = $_POST['services'];
            if (in_array('other', $services))
                array_push($services, $_POST['other_services']);
        }
        if (!empty($services)) {
            $serv_array = array(
                'key' => 'mm365_services_looking_for',
                'value' => $services,
                'compare' => 'IN',
            );
        }

        //Industries
        $indus_array = array();
        $indstry = array();
        if (isset($_POST['industry'])) {
            $indstry = $_POST['industry'];
            if (in_array('other', $indstry))
                array_push($indstry, $_POST['other_industry']);
        }
        if (!empty($indstry)) {
            $indus_array = array(
                'key' => 'mm365_services_industry',
                'value' => $indstry,
                'compare' => 'IN',
            );
        }

        //Certification
        $certification_array = array();
        $certification = array();
        if (isset($_POST['certifications'])) {
            $certification = $_POST['certifications'];
            if (in_array('other', $certification))
                array_push($certification, $_POST['other_certification']);
        }
        if (!empty($certification)) {
            $certification_array = array(
                'key' => 'mm365_certifications',
                'value' => $certification,
                'compare' => 'IN',
            );
        }

        //NAICS Code
        $naics_array = array();
        $naics = array();
        if (isset($_POST['naics_codes'])) {
            $naics = $_POST['naics_codes'];
        }
        $naics_to_search = array_filter($naics, 'strlen');
        if (!empty($naics_to_search)) {
            $naics_array = array(
                'key' => 'mm365_naics_codes',
                'value' => $naics_to_search,
                'compare' => 'IN',
            );
        }

        //International assitance from MMSDC
        $int_assi_array = array();
        if (!empty($_POST['international_assistance_looking_for'])) {
            $int_assi_array = array(
                'key' => 'mm365_match_intassi_lookingfor',
                'value' => $_POST['international_assistance_looking_for'],
                'compare' => 'IN',
            );
        }


        if (!empty($services_required_countries)) {
            $sl_countries_match = array(
                'key' => 'mm365_service_needed_country',
                'value' => $services_required_countries,
                'compare' => 'IN'
            );
        } else
            $sl_countries_match = NULL;

        if (!empty($services_required_states)) {
            $sl_states_match = array(
                'key' => 'mm365_service_needed_state',
                'value' => $services_required_states,
                'compare' => 'IN'
            );
        } else
            $sl_states_match = NULL;


        //Minority category
        if ($minority_code != 'all' and !empty($minority_code)) {
            $minority_code_match = array('key' => 'mm365_mr_mbe_category', 'value' => $minority_code, 'compare' => '=');
        } else {
            $minority_code_match = array();
        }
        //Employees count
        if ($ec_search != '' or !empty($ec_search)) {
            $employee_match = array('key' => 'mm365_number_of_employees', 'value' => $ec_search, 'compare' => $ec_compare);
        } else {
            $employee_match = array();
        }
        //Size of company
        if ($cs_search != '' or !empty($cs_search)) {

            if ($cs_compare == 'NOT IN') {
                $companysize_match =
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => 'mm365_size_of_company',
                            'value' => NULL,
                            'compare' => '!='
                        ),
                        array(
                            'key' => 'mm365_size_of_company',
                            'value' => $cs_search,
                            'compare' => $cs_compare
                        )

                    );
            } else {
                $companysize_match =
                    array(
                        'key' => 'mm365_size_of_company',
                        'value' => $cs_search,
                        'compare' => $cs_compare
                    );
            }


        } else {
            $companysize_match = array();
        }

        //Council filtering
        if (!empty($search_council)) {
            $search_council_para = array(
                'key' => 'mm365_requester_company_council',
                'value' => $search_council,
                'compare' => '=',
            );
        } else
            $search_council_para = array();


        //Date adjust
        //Get data
        $report_query_args = array(
            'posts_per_page' => -1,
            // No limit
            //'fields'         => 'ids', // Reduce memory footprint
            'post_type' => 'mm365_matchrequests',
            'post_status' => array('publish'),
            'meta_query' => array(

                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'mm365_matched_companies_last_updated_isodate',
                        'value' => array($from_date, $to_date),
                        'compare' => 'BETWEEN',
                        'type' => 'DATE'
                    ),
                    $match_status_search,
                    $closure_reason_filter,
                    $serv_array,
                    $indus_array,
                    $minority_code_match,
                    $sl_countries_match,
                    $sl_states_match,
                    $naics_array,
                    $certification_array,
                    $employee_match,
                    $companysize_match,
                    $int_assi_array,
                    $search_council_para,
                    $super_buyer_filter
                ),

            )

        );



        $report_query = new \WP_Query($report_query_args);
        $found_results = $report_query->found_posts;

        if ($found_results <= 0) {
               /*If no results redirect to respecitve pages with forms */
               setcookie('report_generate_status', 'err', time() + 3600, "/","");
               wp_redirect(site_url($redirect_slug));
               die();
        }

        //Check result counts
        if ($found_results > 0) {
            $data = array();
            // /mm365_services_details

            while ($report_query->have_posts()):
                $report_query->the_post();

                //services
                $services = array();

                $ar_services_list = (get_post_meta(get_the_ID(), 'mm365_services_looking_for'));
                if (!empty($ar_services_list)) {
                    $services = array();
                    foreach ($ar_services_list as $key => $value) {
                        $services[] = $value;
                    }
                    if (isset($services)):
                        $services_list = '';
                        foreach ($services as $service) {
                            $services_list .= "\n" . '• ' . $service . '';
                        }
                    endif;
                    $services = array();
                } else {
                    $services_list = '-';
                }


                //Industries
                if (!empty((get_post_meta(get_the_ID(), 'mm365_services_industry')))):
                    foreach ((get_post_meta(get_the_ID(), 'mm365_services_industry')) as $key => $value) {
                        $industries[] = $value;
                    }
                    if (isset($industries)):
                        $industries_list = '';
                        foreach ($industries as $industry) {
                            $industries_list .= "\n" . '• ' . $industry . '';
                        }
                    endif;
                    $industries = array();
                else:
                    $industries_list = '-';
                endif;



                //Location
                $location_searching = get_post_meta(get_the_ID(), 'mm365_location_for_search', true);
                $breaks = array("<br />", "<br>", "<br/>");
                $service_required_locations = str_ireplace($breaks, "\r\n", $location_searching);


                //Company size
                $employee_count = get_post_meta(get_the_ID(), 'mm365_number_of_employees', true);
                if ($employee_count == '&lt; 20'):
                    $ec = "< 20";
                else:
                    $ec = $employee_count;
                endif;

                $size = get_post_meta(get_the_ID(), 'mm365_size_of_company', true);
                if ($size == '&lt;$100,000'):
                    $company_size = "< $100,000";
                else:
                    $company_size = $size;
                endif;

                //Certification
                $ar_certification = (get_post_meta(get_the_ID(), 'mm365_certifications'));
                if (!empty($ar_certification)) {
                    $certifications = array();
                    foreach ($ar_certification as $key => $value) {
                        $certifications[] = $value;
                    }
                    if (isset($certifications)):
                        $certifications_list = '';
                        foreach ($certifications as $certificate) {
                            $certifications_list .= "\n" . '• ' . $certificate . '';
                        }
                    endif;
                    $certifications = array();
                } else {
                    $certifications_list = '-';
                }

                //NAICS Codes
                $naics = array();
                if (!empty((get_post_meta(get_the_ID(), 'mm365_naics_codes')))):
                    foreach ((get_post_meta(get_the_ID(), 'mm365_naics_codes')) as $key => $value) {
                        $naics[] = $value;
                    }
                    if (isset($naics)):
                        $naics_list = '';
                        foreach ($naics as $naic) {
                            if ($naic != '') {
                                $naics_list .= "\n" . '• ' . $naic . '';
                            }
                        }
                    endif;
                    $naics = array();
                else:
                    $naics_list = '-';
                endif;

                //Int assi looking for
                if (!empty((get_post_meta(get_the_ID(), 'mm365_match_intassi_lookingfor')))):
                    foreach ((get_post_meta(get_the_ID(), 'mm365_match_intassi_lookingfor')) as $key => $value) {
                        $looking_for[] = $value;
                    }
                    if (isset($looking_for)):
                        $looking_for_list = '';
                        foreach ($looking_for as $ldata) {
                            $looking_for_list .= "\n" . '• ' . $ldata . '';
                        }
                    endif;
                    $looking_for = array();
                else:
                    $looking_for_list = '-';
                endif;

                //Matched Companies
                if (get_post_meta(get_the_ID(), 'mm365_matched_companies', true) != '') {

                    foreach (maybe_unserialize(get_post_meta(get_the_ID(), 'mm365_matched_companies', true)) as $key => $value) {
                        $matched_companies[] = $this->replace_html_in_companyname(get_the_title($value[0]));
                    }

                    if (isset($matched_companies)):
                        $matched_companies_list = '';
                        foreach ($matched_companies as $company) {
                            if ($company != '') {
                                $matched_companies_list .= "\n" . '• ' . $company . '';
                            }
                        }
                    endif;
                    $matched_companies = array();
                } else {
                    $matched_companies_list = '-';
                }

                //Minority Codes List
                if (!empty((get_post_meta(get_the_ID(), 'mm365_mr_mbe_category')))) {
                    foreach ((get_post_meta(get_the_ID(), 'mm365_mr_mbe_category')) as $key => $value) {
                        $mincode[] = $this->expand_minoritycode($value);
                    }
                    if (isset($mincode)):
                        $minority_codes_list = '';
                        foreach ($mincode as $minority) {
                            $minority_codes_list .= "\n" . '• ' . $minority . '';
                        }
                    endif;
                    $mincode = array();
                } else {
                    $minority_codes_list = '-';
                }

                $status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);
                $hide_closure = 0;
                $hide_closure_message = 0;

                switch ($status) {
                    case 'auto-approved':
                        $status = 'Auto Approved';
                        break;
                    case 'nomatch':
                        $status = 'No Match';
                        break;
                    default:
                        $status = ucfirst($status);
                        break;
                }

                if ($match_status == 'all' or $match_status == 'completed' or $match_status == 'cancelled') {
                    $hide_closure = 30;
                    $hide_closure_message = 50;
                }
                if ($match_status == 'completed') {
                    $reason_for_label = 'completion';
                } elseif ($match_status == 'cancelled') {
                    $reason_for_label = 'cancellation';
                } else {
                    $reason_for_label = 'closure';
                }

                //Get Council Details
                $cmp_council_id = get_post_meta(get_the_ID(), 'mm365_requester_company_council', true);
                $council_name = get_the_title($cmp_council_id);
                $council_short_name = get_post_meta($cmp_council_id, 'mm365_council_shortname', true);

                $approver = get_post_meta(get_the_ID(), 'mm365_matched_companies_approved_by', true);
                $approved_time = get_post_meta(get_the_ID(), 'mm365_matched_companies_approved_time', true);
                $details = get_post_meta(get_the_ID(), 'mm365_services_details', true);
                $created_date = get_post_time("m/d/Y h:i A");
                $modified_time = get_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated', true);

                if (is_numeric($approver)) {
                    $approved_by = get_userdata($approver)->user_login;
                } else {
                    $approved_by = "";
                }
                if ($approved_time != '') {
                    $approved_time = wp_date('m/d/Y g:i A', $approved_time);
                }

                $requester_company_id = get_post_meta(get_the_ID(), 'mm365_requester_company_id', true);
                $requester_company = get_the_title($requester_company_id);

                $reason_for_closure = get_post_meta(get_the_ID(), 'mm365_reason_for_closure_filter', true);
                $closure_message = get_post_meta(get_the_ID(), 'mm365_reason_for_closure', true);


                //Check if buyer
                if (get_post_meta($requester_company_id, 'mm365_service_type', true) == 'seller') {
                    $certified = (get_post_meta($requester_company_id, 'mm365_certification_status', true) == 'verified') ? 'Yes' : 'No';
                } else {
                    $certified = 'NA';
                }

                $matchrequest = array(
                    $this->replace_html_in_companyname($requester_company),
                    $council_short_name,
                    $certified,
                    $details,
                    $service_required_locations,
                    $services_list,
                    $industries_list,
                    $minority_codes_list,
                    ($ec ?: '-'),
                    ($company_size ?: '-'),
                    $certifications_list,
                    $naics_list,
                    $looking_for_list,
                    $status,
                    $reason_for_closure ?: '-',
                    $closure_message ?: '-',
                    $matched_companies_list,
                    ($approved_by ?: '-'),
                    $created_date,
                    $modified_time,
                    ($approved_time ?: '-'),
                );
                array_push($data, $matchrequest);
            endwhile;


            $writer_2 = new XLSXWriter();


            $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 50, 'wrap_text' => true);
            $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 45, 'wrap_text' => true);
            $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');

            $writer_2->writeSheetHeader(
                'Sheet1',
                array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string', '9' => 'string', '10' => 'string', '11' => 'string', '12' => 'string', '13' => 'string', '14' => 'string', '15' => 'string', '15' => 'string', '16' => 'string', '17' => 'string', '18' => 'string', '19' => 'string'),
                $col_options = [
                    'widths' => [50, $counil_col_width, 20, 40, 40, 30, 30, 30, 30, 30, 30, 30, 30, 30, $hide_closure, $hide_closure_message, 30, 30, 30],
                    'suppress_row' => true
                ]
            );

            $writer_2->writeSheetRow(
                'Sheet1',
                array(
                    "Match Requests Submitted \n" .
                    'From ' . date_format(date_create($from_date), 'm/d/Y') . " " .
                    'To ' . date_format(date_create($to_date), 'm/d/Y')
                ),
                $styles1
            );

            $writer_2->writeSheetRow(
                'Sheet1',
                array(
                    'Requester company',
                    'Requester council',
                    'Certfied',
                    'Request details',
                    'Location where products or services are required',
                    'Services or products',
                    'Industry',
                    'Minority classification',
                    'Number of employees',
                    'Size of company',
                    'Industry Certifications',
                    'NAICS codes',
                    'Looking for international assistance',
                    'Match status',
                    'Reason for ' . $reason_for_label,
                    'Message',
                    'Matched companies',
                    'Approved by',
                    'Created date',
                    'Modified date',
                    'Time of approval'
                ),
                $styles2
            );

            foreach ($data as $dat) {
                $writer_2->writeSheetRow('Sheet1', $dat, $styles3);
            }

            $file_2 = 'matchrequests-report-' . time() . '.xlsx';
            $writer_2->writeToFile($file_2);

            if (file_exists($file_2)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file_2) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_2));
                readfile($file_2);
                unlink($file_2);
                exit;
            }
            wp_reset_postdata();
            die();
        }

    }


    /**
     * @param int $mr_id
     * Download match result 
     * 
     * 
     */


    function mm365_matchresults_download($mr_id)
    {

        //Find Matched Comapnies using MR id
        $matched_companies = array();
        foreach (maybe_unserialize(get_post_meta($mr_id, 'mm365_matched_companies', true)) as $key => $value) {
            //Look for approved matches
            if ($value[1] == 1) {
                array_push($matched_companies, $value[0]);
            }
        }

        $mrdownload_args = array(
            'posts_per_page' => -1,    // No limit
            'post_type' => 'mm365_companies',
            'post__in' => $matched_companies,
            'orderby' => 'post__in',
        );

        $file_name = "Match Results for #" . $mr_id;

        $quick_report_query = new \WP_Query($mrdownload_args);

        $data = array();

        while ($quick_report_query->have_posts()):
            $quick_report_query->the_post();

            //services
            if (!empty((get_post_meta(get_the_ID(), 'mm365_services')))):
                $services_list = '';
                foreach (get_post_meta(get_the_ID(), 'mm365_services') as $service) {
                    $services_list .= "\n" . '• ' . $service . '';
                }
            else:
                $services_list = '-';
            endif;

            //Industries
            if (!empty((get_post_meta(get_the_ID(), 'mm365_industry')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_industry')) as $key => $value) {
                    $industries[] = $value;
                }
                if (isset($industries)):
                    $industries_list = '';
                    foreach ($industries as $industry) {
                        $industries_list .= "\n" . '• ' . $industry . '';
                    }
                endif;
                $industries = array();
            else:
                $industries_list = '-';
            endif;

            //Location
            $city = $this->get_cityname(get_post_meta(get_the_ID(), 'mm365_company_city', true));
            $state = $this->get_statename(get_post_meta(get_the_ID(), 'mm365_company_state', true));
            $country = $this->get_countryname(get_post_meta(get_the_ID(), 'mm365_company_country', true));


            //Company size
            $employee_count = get_post_meta(get_the_ID(), 'mm365_number_of_employees', true);
            if ($employee_count == '&lt; 20'):
                $ec = "< 20";
            else:
                $ec = $employee_count;
            endif;

            $size = get_post_meta(get_the_ID(), 'mm365_size_of_company', true);
            if ($size == '&lt;$100,000'):
                $company_size = "< $100,000";
            else:
                $company_size = $size;
            endif;


            //Certification
            $ar_certification = (get_post_meta(get_the_ID(), 'mm365_certifications'));
            if (!empty($ar_certification)) {
                $certifications = array();
                foreach ($ar_certification as $key => $value) {
                    $certifications[] = $value;
                }
                if (isset($certifications)):
                    $certifications_list = '';
                    foreach ($certifications as $certificate) {
                        $certifications_list .= "\n" . '• ' . $certificate . '';
                    }
                endif;
                $certifications = array();
            } else {
                $certifications_list = '-';
            }

            //NAICS Codes
            if (!empty((get_post_meta(get_the_ID(), 'mm365_naics_codes')))) {
                foreach ((get_post_meta(get_the_ID(), 'mm365_naics_codes')) as $key => $value) {
                    $naics[] = $value;
                }
                if (isset($naics)):
                    $naics_list = '';
                    foreach ($naics as $naic) {
                        if ($naic != '') {
                            $naics_list .= "\n" . '• ' . $naic . '';
                        }
                    }
                endif;
                $naics = array();
            } else {
                $naics_list = '-';
            }

            //Int assi looking for
            if (!empty((get_post_meta(get_the_ID(), 'mm365_international_assistance')))) {
                foreach ((get_post_meta(get_the_ID(), 'mm365_international_assistance')) as $key => $value) {
                    $looking_for[] = $value;
                }
                if (isset($looking_for)):
                    $looking_for_list = '';
                    foreach ($looking_for as $ldata) {
                        $looking_for_list .= "\n" . '• ' . $ldata . '';
                    }

                endif;
                $looking_for = array();
            } else {
                $looking_for_list = '-';
            }


            //Type
            $service_type = $this->get_company_service_type(get_post_meta(get_the_ID(), 'mm365_service_type', true));
            $minority_code = $this->expand_minoritycode(get_post_meta(get_the_ID(), 'mm365_minority_category', true));

            $description = get_post_meta(get_the_ID(), 'mm365_company_description', true);
            $contact_person = get_post_meta(get_the_ID(), 'mm365_contact_person', true);
            $contact_address = get_post_meta(get_the_ID(), 'mm365_company_address', true);
            $contact_phone = get_post_meta(get_the_ID(), 'mm365_company_phone', true);
            $contact_email = get_post_meta(get_the_ID(), 'mm365_company_email', true);

            $alt_phone = get_post_meta(get_the_ID(), 'mm365_alt_phone', true);
            $alt_email = get_post_meta(get_the_ID(), 'mm365_alt_email', true);
            $alt_contact = get_post_meta(get_the_ID(), 'mm365_alt_contact_person', true);
            $zip_code = get_post_meta(get_the_ID(), 'mm365_zip_code', true);
            $website = get_post_meta(get_the_ID(), 'mm365_website', true);
            $created_date = get_post_time("m/d/Y h:i A");
            $updated_date = get_the_modified_time("m/d/Y h:i A");

            //Get Council Details
            $cmp_council_id = get_post_meta(get_the_ID(), 'mm365_company_council', true);
            $council_name = get_the_title($cmp_council_id);
            $council_short_name = get_post_meta($cmp_council_id, 'mm365_council_shortname', true);

            //Capability statements
            $capability_statements = "";
            if (get_post_meta(get_the_ID(), 'mm365_company_docs', true) != '') {
                foreach (get_post_meta(get_the_ID(), 'mm365_company_docs', true) as $attachment_id => $attachment_url) {
                    $capability_statements .= '• ' . basename(get_attached_file($attachment_id)) . "\n\n";
                }
            } else {
                $capability_statements = "-";
            }

            $mc = json_decode(get_post_meta(get_the_ID(), 'mm365_main_customers', true));
            if (!empty($mc)) {
                $current_customers = array();
                foreach ($mc as $key => $value) {
                    //$current_customers[] = $value;
                    if ($value != '') {
                        array_push($current_customers, $value);
                    }
                }
                $customers = '';
                foreach ($current_customers as $customer) {
                    $customers .= "\n" . '• ' . $customer . '';
                }
            } else {
                $customers = '-';
            }

            if ($website != '') {
                if (!filter_var($website, FILTER_VALIDATE_URL)) {
                    $website = '=HYPERLINK("http://' . $website . '", "' . $website . '")';
                } else {
                    $website = '=HYPERLINK("' . $website . '", "' . $website . '")';
                }
            }


            if (!empty(get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_countries'))) {
                $breaks = array("<br />", "<br>", "<br/>");
                $countries = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_countries');
                $states = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_states');
                $locations = str_ireplace($breaks, "\r\n", $this->multi_countries_state_display($countries, $states));
                $serviceable_locations = $locations;
            } else {
                $serviceable_locations = "-";
            }

            $company = array(
                $this->replace_html_in_companyname(get_the_title()),
                (get_post_meta(get_the_ID(), 'mm365_certification_status', true) == 'verified') ? 'Yes' : 'No',
                $council_short_name,
                htmlspecialchars_decode(strip_tags($description)),
                $services_list,
                $service_type,
                $minority_code,
                $serviceable_locations,
                $capability_statements,
                $looking_for_list,
                $industries_list,
                ($website ?: '-'),
                $contact_person,
                $contact_address,
                $contact_phone,
                $contact_email,
                rtrim($city, ','),
                $state,
                $country,
                $zip_code,
                ($alt_contact ?: '-'),
                ($alt_phone ?: '-'),
                ($alt_email ?: '-'),
                ($ec ?: '-'),
                ($company_size ?: '-'),
                ($customers ?: '-'),
                $certifications_list,
                $naics_list,
                $created_date,
                $updated_date
            );

            //$company = array(get_the_title(),$services_list,$service_type,$industries_list,rtrim($city,','),$state,$country,$employee_count,$size,"","");
            array_push($data, $company);


        endwhile;

        $writer = new XLSXWriter();

        $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 30, 'wrap_text' => true);
        $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 30, 'wrap_text' => true);
        $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');
        $styles4 = array(
            ['border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'font' => 'Arial', 'font-size' => 10, 'height' => 30, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'left', 'valign' => 'top', 'wrap_text' => true],
            ['border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'font' => 'Arial', 'font-size' => 10, 'fill' => '#fff', 'color' => '#000', 'halign' => 'left', 'valign' => 'top', 'wrap_text' => true]
        );

        $writer->writeSheetHeader(
            'Sheet1',
            array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string', '9' => 'string', '10' => 'string', '11' => 'string', '12' => 'string', '13' => 'string', '14' => 'string', '15' => 'string', '16' => 'string', '17' => 'string', '18' => 'string', '19' => 'string', '20' => 'string', '21' => 'string', '22' => 'string', '23' => 'string', '24' => 'string', '25' => 'string', '26' => 'string', '27' => 'string', '28' => 'string', '29' => 'string', '30' => 'string'),
            $col_options = ['widths' => [30, 20, 20, 50, 40, 30, 30, 50, 50, 50, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30], 'suppress_row' => true]
        );

        $writer->writeSheetRow('Sheet1', array("Match request #" . $mr_id, " "), $styles1);
        $writer->writeSheetRow('Sheet1', array("Requested date & time", get_the_modified_time("m/d/Y h:i A", $mr_id)), $styles4);
        $mr_services = implode(', ', (get_post_meta($mr_id, 'mm365_services_looking_for')));
        if ($mr_services == '') {
            $mr_services = '-';
        }
        $writer->writeSheetRow('Sheet1', array("Services required", $mr_services), $styles4);
        $mr_industry = implode(', ', (get_post_meta($mr_id, 'mm365_services_industry')));
        if ($mr_industry == '') {
            $mr_industry = '-';
        }
        $writer->writeSheetRow('Sheet1', array("Industries", $mr_industry), $styles4);

        $mcodes = '';
        $minority_categories = (get_post_meta($mr_id, 'mm365_mr_mbe_category'));
        if (!empty($minority_categories)) {
            $cnt = 0;
            foreach ($minority_categories as $key => $value) {
                $mcodes .= $this->expand_minoritycode($value);
                $cnt++;
                if (count($minority_categories) > $cnt) {
                    $mcodes .= ", ";
                }
            }
        } else {
            $mcodes = "-";
        }
        $writer->writeSheetRow('Sheet1', array("Minority classification", $mcodes), $styles4);



        $serviceable_location = get_post_meta($mr_id, 'mm365_location_for_search', true);
        $line_breaks = array("<br />", "<br>", "<br/>");
        $purging_html_selo = str_ireplace($line_breaks, "\r\n", $serviceable_location);
        $writer->writeSheetRow('Sheet1', array("Location where the service or products are required", $purging_html_selo), $styles4);



        $certifications = (get_post_meta($mr_id, 'mm365_certifications'));
        if (!empty($certifications) or $certifications != ''):
            foreach ($certifications as $key => $value) {
                $mm365_certifications[] = $value;
            }
            if (isset($mm365_certifications)):
                $mr_certifications = implode(', ', $mm365_certifications);
            else:
                $mr_certifications = "-";
            endif;
        endif;
        $writer->writeSheetRow('Sheet1', array("Certifications", $mr_certifications), $styles4);


        foreach ((get_post_meta($mr_id, 'mm365_naics_codes')) as $key => $value) {
            $naics[] = $value;
        }
        if (!empty($naics)):
            $mr_naics = implode(', ', $naics);
        else:
            $mr_naics = "-";
        endif;
        $writer->writeSheetRow('Sheet1', array("NAICS codes", $mr_naics), $styles4);

        $employee_count = get_post_meta($mr_id, 'mm365_number_of_employees', true);
        if ($employee_count == '&lt; 20'):
            $current_number_of_employees = "< 20";
        else:
            $current_number_of_employees = $employee_count;
        endif;

        $size = get_post_meta($mr_id, 'mm365_size_of_company', true);
        if ($size == '&lt;$100,000'):
            $current_size_of_company = "<$100,000";
        else:
            $current_size_of_company = $size;
        endif;


        $intassi_match = get_post_meta(get_the_ID(), 'mm365_match_intassi_lookingfor');
        if (!empty($intassi_match)) {
            $intassi_match_looking = implode(', ', $intassi_match);
        } else {
            $intassi_match_looking = '';
        }


        $writer->writeSheetRow('Sheet1', array("Size of company (Annual Sales In \$USD)", ($current_size_of_company ?: '-')), $styles4);
        $writer->writeSheetRow('Sheet1', array("Number of employees", ($current_number_of_employees ?: '-')), $styles4);
        $writer->writeSheetRow('Sheet1', array("Looking for international assistance", ($intassi_match_looking ?: '-')), $styles4);
        $writer->writeSheetRow('Sheet1', array("Details of products or services you are looking For	", get_post_meta($mr_id, 'mm365_services_details', true)), $styles4);


        $writer->writeSheetRow('Sheet1', array("Match results", ""), $styles1);
        $writer->writeSheetRow(
            'Sheet1',
            array(
                'Company name',
                'Certified',
                'Council',
                'Description',
                'Company Services',
                'Service type',
                'Minority classification',
                'Locations where services or products are available',
                'Capability statement',
                'International Assistance from council',
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
                'Updated date'
            ),
            $styles2
        );

        foreach ($data as $dat) {
            $writer->writeSheetRow('Sheet1', $dat, $styles3);
        }

        $file = $file_name . '.xlsx';
        $writer->writeToFile($file);

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);

            exit;
        }


        //Function ends here

    }



    /**
     * @param int $author_id
     * @param string $period
     * 
     */

    function mm365_matchrequests_download($author_id, $period)
    {


        switch ($period) {
            case 'two_week':
                $period_filter = "14 days ago";
                $filter_title = " last two weeks";
                break;
            case 'month':
                $period_filter = "1 month ago";
                $filter_title = " last one month";
                break;
            case 'six_months':
                $period_filter = "6 months ago";
                $filter_title = " last six months";
                break;
            case 'year':
                $period_filter = "1 year ago";
                $filter_title = " last one year";
                break;
            default:
                $period_filter = "14 days ago";
                $filter_title = " last two weeks";
                break;
        }
        $quickreports_match_args = array(
            'posts_per_page' => -1,    // No limit
            'post_type' => 'mm365_matchrequests',
            'post_status' => array('publish'),
            'author' => $author_id,
            'meta_query' => array(
                array(
                    'key' => 'mm365_requester_company_id',
                    'value' => esc_html($_COOKIE['active_company_id']),
                    'compare' => '='
                )
            ),
            'date_query' => array(
                array('column' => 'post_modified', 'after' => $period_filter)
            )
        );
        $file_name = "Match requests submitted in " . $filter_title;

        $data = array();


        $report_query = new \WP_Query($quickreports_match_args);

        while ($report_query->have_posts()):
            $report_query->the_post();


            //services
            if (!empty((get_post_meta(get_the_ID(), 'mm365_services_looking_for')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_services_looking_for')) as $key => $value) {
                    $services[] = $value;
                }
                if (isset($services)):
                    $services_list = '';
                    foreach ($services as $service) {
                        $services_list .= "\n" . '• ' . $service . '';
                    }
                endif;
                $services = array();
            else:
                $services_list = '-';
            endif;

            //Industries
            if (!empty((get_post_meta(get_the_ID(), 'mm365_services_industry')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_services_industry')) as $key => $value) {
                    $industries[] = $value;
                }
                if (isset($industries)):
                    $industries_list = '';
                    foreach ($industries as $industry) {
                        $industries_list .= "\n" . '• ' . $industry . '';
                    }
                endif;
                $industries = array();
            else:
                $industries_list = '-';
            endif;



            //Location
            $location_searching = get_post_meta(get_the_ID(), 'mm365_location_for_search', true);
            $breaks = array("<br />", "<br>", "<br/>");
            $service_required_locations = str_ireplace($breaks, "\r\n", $location_searching);

            //Company size
            $employee_count = get_post_meta(get_the_ID(), 'mm365_number_of_employees', true);
            if ($employee_count == '&lt; 20'):
                $ec = "< 20";
            else:
                $ec = $employee_count;
            endif;
            $size = get_post_meta(get_the_ID(), 'mm365_size_of_company', true);
            if ($size == '&lt;$100,000'):
                $company_size = "< $100,000";
            else:
                $company_size = $size;
            endif;

            //Certification
            $ar_certification = (get_post_meta(get_the_ID(), 'mm365_certifications'));
            if (!empty($ar_certification)) {
                $certifications = array();
                foreach ($ar_certification as $key => $value) {
                    $certifications[] = $value;
                }
                if (isset($certifications)):
                    $certifications_list = '';
                    foreach ($certifications as $certificate) {
                        $certifications_list .= "\n" . '• ' . $certificate . '';
                    }
                endif;
                $certifications = array();
            } else {
                $certifications_list = '-';
            }

            //NAICS Codes
            if (!empty((get_post_meta(get_the_ID(), 'mm365_naics_codes')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_naics_codes')) as $key => $value) {
                    $naics[] = $value;
                }
                if (isset($naics)):
                    $naics_list = '';
                    foreach ($naics as $naic) {
                        if ($naic != '') {
                            $naics_list .= "\n" . '• ' . $naic . '';
                        }
                    }
                endif;
                $naics = array();
            else:
                $naics_list = '-';
            endif;


            $matched_companies_list = '-';
            if (get_post_meta(get_the_ID(), 'mm365_matched_companies', true) != ''):
                foreach (maybe_unserialize(get_post_meta(get_the_ID(), 'mm365_matched_companies', true)) as $key => $value) {
                    if ($value[1] == 1) {
                        $matched_companies[] = $this->replace_html_in_companyname(get_the_title($value[0]));
                    }
                }
                if (!empty($matched_companies)):
                    $matched_companies_list = '';
                    foreach ($matched_companies as $company) {
                        if ($company != '') {
                            $matched_companies_list .= "\n" . '• ' . $company . '';
                        }
                    }
                endif;
                $matched_companies = array();
            else:
                $matched_companies_list = '-';
            endif;

            //Minority Codes List
            if (!empty((get_post_meta(get_the_ID(), 'mm365_mr_mbe_category')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_mr_mbe_category')) as $key => $value) {
                    $mincode[] = $this->expand_minoritycode($value);
                }
                if (isset($mincode)):
                    $minority_codes_list = '';
                    foreach ($mincode as $minority) {
                        $minority_codes_list .= "\n" . '• ' . $minority . '';
                    }
                endif;
                $mincode = array();
            else:
                $minority_codes_list = '-';
            endif;

            //Int assi looking for
            if (!empty((get_post_meta(get_the_ID(), 'mm365_match_intassi_lookingfor')))):
                foreach ((get_post_meta(get_the_ID(), 'mm365_match_intassi_lookingfor')) as $key => $value) {
                    $looking_for[] = $value;
                }
                if (isset($looking_for)):
                    $looking_for_list = '';
                    foreach ($looking_for as $ldata) {
                        $looking_for_list .= "\n" . '• ' . $ldata . '';
                    }

                endif;
                $looking_for = array();
            else:
                $looking_for_list = '-';
            endif;


            $status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);
            $approver = get_post_meta(get_the_ID(), 'mm365_matched_companies_approved_by', true);
            $approved_time = get_post_meta(get_the_ID(), 'mm365_matched_companies_approved_time', true);
            $details = get_post_meta(get_the_ID(), 'mm365_services_details', true);
            $created_time = get_post_time("m/d/Y h:i A");
            $modified_time = get_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated', true);

            if ($approver != '') {
                $approver = get_userdata($approver)->user_login;
            }

            if ($approved_time != '') {
                $approved_time = wp_date('m/d/Y g:i A', $approved_time);
            }
            $requester_company = get_the_title(get_post_meta(get_the_ID(), 'mm365_requester_company_id', true));

            $matchrequest = array(
                get_the_ID(),
                $details,
                $services_list,
                $industries_list,
                $minority_codes_list,
                $service_required_locations,
                ($ec ?: '-'),
                ($company_size ?: '-'),
                $certifications_list,
                $naics_list,
                $looking_for_list,
                ucfirst($status),
                $matched_companies_list,
                ($approver ?: '-'),
                $created_time,
                $modified_time,
                ($approved_time ?: '-')
            );
            array_push($data, $matchrequest);
        endwhile;


        $writer_2 = new XLSXWriter;

        $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 30, 'wrap_text' => true);
        $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 20, 'wrap_text' => true);
        $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');

        $writer_2->writeSheetHeader('Sheet1', array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string', '9' => 'string', '10' => 'string', '11' => 'string', '12' => 'string', '13' => 'string', '14' => 'string', '15' => 'string', '16' => 'string'), $col_options = ['widths' => [30, 40, 40, 50, 30, 50, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30,], 'suppress_row' => true]);

        $writer_2->writeSheetRow('Sheet1', $rowdata = array($file_name, 'From ' . date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))) . $period_filter)) . ' To ' . date('m/d/Y', time())), $styles1);
        $writer_2->writeSheetRow('Sheet1', $rowdata = array('Match request ID', 'Request details', 'Services or products required', 'Industry', 'Minority classifications', 'Location where products or services are required', 'Number of employees', 'Size of company', 'Industry Certifications', 'NAICS codes', 'Looking for', 'Match status', 'Matched companies', 'Approved by', 'Created date', 'Modified date', 'Time of approval'), $styles2);

        foreach ($data as $dat) {
            $writer_2->writeSheetRow('Sheet1', $dat, $styles3);
        }
        //$writer_2->writeSheet($data);

        $file_2 = $file_name . '.xlsx';
        $writer_2->writeToFile($file_2);

        if (file_exists($file_2)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_2) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_2));
            readfile($file_2);
            unlink($file_2);
            exit;
        }


    }



}
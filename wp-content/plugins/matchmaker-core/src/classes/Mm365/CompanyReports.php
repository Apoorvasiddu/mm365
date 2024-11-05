<?php

namespace Mm365;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class CompanyReports
{

    use CertificateAddon;
    use CompaniesAddon;
    use CountryStateCity;
    use CouncilAddons;
    use ReusableMethods;

    function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'assets'), 11);

        add_action('wp_ajax_mm365_admin_viewreport_companies', array($this, 'quick_view'), 11);
        add_action('wp_ajax_mm365_admin_viewreport_companies_filtered', array($this, 'filtered_view'), 11);

        add_filter('mm365_admin_quickreports_companies', array($this, 'quick_download'), 11, 3);
        add_filter('mm365_admin_filteredreports_companies', array($this,'filtered_download'),10,0);
    }

    /**
     * 
     * 
     * 
     */
    function assets()
    {

        $localize = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        );
        wp_register_script('admin_list_admin_viewreport_companies', plugins_url('matchmaker-core/assets/admin_view_companies_report.js'), array('jquery'), false, true);
        wp_localize_script('admin_list_admin_viewreport_companies', 'adminViewReportAjax', $localize);
        wp_enqueue_script('admin_list_admin_viewreport_companies');

        wp_register_script('admin_list_admin_viewreport_companies_filtered', plugins_url('matchmaker-core/assets/admin_view_companies_report_filtered.js'), array('jquery'), false, true);
        wp_localize_script('admin_list_admin_viewreport_companies_filtered', 'adminViewReportFilteredAjax', $localize);
        wp_enqueue_script('admin_list_admin_viewreport_companies_filtered');

    }

    /**
     * 
     * 
     * 
     */
    function quick_view()
    {

        $user = wp_get_current_user();
        $council_id = $this->get_userDC($user->ID);
        $request = $_GET;
        $period = $_REQUEST['period'];
        $meta = $_REQUEST['companymeta'];

        //If current user is not council manager, check if super admin is filterin view with specific council
        $is_admin_filtering = 'no';
        if ($_REQUEST['sa_council_filter'] != '' and $council_id == '') {
            $is_admin_filtering = 'yes';
        }

        //If admin ovverride council id with filtering council id chosen by admin
        if ($council_id == '') {
            $council_id = $_REQUEST['sa_council_filter'];
        }


        //If admin is not filtering or selected all council, this will be skipped in council_id check below
        if ($meta != 'x') {
            $meta_additional = array(
                'key' => 'mm365_service_type',
                'value' => $meta,
                'compare' => '=',
            );
        } else
            $meta_additional = '';

        if ($council_id != '') {
            $council_filter = array(
                'key' => 'mm365_company_council',
                'value' => $council_id,
                'compare' => '=',
            );
        } else
            $council_filter = array();


        $args = array(
            'post_type' => 'mm365_companies',
            'post_status' => 'publish',
            'posts_per_page' => $request['length'],
            'offset' => $request['start'],
            'order' => 'DESC',
            'order_by' => 'modified',
            'date_query' => array(
                array('after' => '1 ' . $period . ' ago')
            ),

        );

        if (isset($request['order'])):
            if ($request['order'][0]['column'] == 0 and $request['order'][0]['dir'] != '') {
                $args['orderby'] = array('title' => $request['order'][0]['dir']);
            }
        endif;

        if (!empty($request['search']['value'])) {

            //Cities search
            $cities = $this->find_city($request['search']['value']);
            $cities_matched = array();
            foreach ($cities as $key => $value) {
                array_push($cities_matched, $value->id);
            }
            $look_cities = array();
            if (!empty($cities_matched)) {
                $look_cities = array(
                    'key' => 'mm365_company_city',
                    'value' => $cities_matched,
                    'compare' => 'IN'
                );
            }

            //states search
            $states = $this->find_state($request['search']['value']);
            $states_matched = array();
            foreach ($states as $key => $value) {
                array_push($states_matched, $value->id);
            }
            $look_states = array();
            if (!empty($states_matched)) {
                $look_states = array(
                    'key' => 'mm365_company_state',
                    'value' => $states_matched,
                    'compare' => 'IN'
                );
            }

            //countries search
            $countries = $this->find_country($request['search']['value']);
            $countries_matched = array();
            foreach ($countries as $key => $value) {
                array_push($countries_matched, $value->id);
            }
            $look_countries = array();
            if (!empty($countries_matched)) {
                $look_countries = array(
                    'key' => 'mm365_company_country',
                    'value' => $countries_matched,
                    'compare' => 'IN'
                );
            }

            $parameter_search = array(
                'relation' => 'OR',
                $look_cities,
                $look_states,
                $look_countries,
                array(
                    'key' => 'mm365_services',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_industry',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_contact_person',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),

                array(
                    'key' => 'mm365_company_email',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_website',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_company_council',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_company_name',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                )
            );


        } else {
            $parameter_search = '';
        }

        $args['meta_query'] = array(
            'relation' => 'AND',
            $meta_additional,
            $parameter_search,
            $council_filter
        );

        $companies_query = new \WP_Query($args);

        $totalData = $companies_query->found_posts;
        $wrong_found = 0;


        if ($companies_query->have_posts()) {
            while ($companies_query->have_posts()) {

                $companies_query->the_post();
                //services
                if (!empty(get_post_meta(get_the_ID(), 'mm365_services'))):
                    //$services_list =  implode( ', ', get_post_meta( get_the_ID(), 'mm365_services' ));
                    $services_list = '';
                    foreach (get_post_meta(get_the_ID(), 'mm365_services') as $service) {
                        if ($service != '')
                            $services_list .= "<div class='intable_span'>" . $service . '</div>';
                    }
                else:
                    $services_list = '-';
                endif;

                //Industries
                if (!empty((get_post_meta(get_the_ID(), 'mm365_industry')))):
                    foreach ((get_post_meta(get_the_ID(), 'mm365_industry')) as $key => $value) {
                        if ($value != '')
                            $industries[] = $value;
                    }
                    if (isset($industries)):
                        $industries_list = '';
                        foreach ($industries as $industry) {
                            $industries_list .= "<div class='intable_span'>" . $industry . '</div>';
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
                //Contact
                $contact_person = get_post_meta(get_the_ID(), 'mm365_contact_person', true);
                $contact_phone = get_post_meta(get_the_ID(), 'mm365_company_phone', true);
                $contact_email = get_post_meta(get_the_ID(), 'mm365_company_email', true);
                $website = strtolower(get_post_meta(get_the_ID(), 'mm365_website', true));

                //Get Council Details
                $company_council_id = get_post_meta(get_the_ID(), 'mm365_company_council', true);
                $council_short_name = get_post_meta($company_council_id, 'mm365_council_shortname', true);

                if ($website != '') {
                    if (filter_var($website, FILTER_VALIDATE_URL)) {
                        $web = "<a href='" . $website . "' target='_blank'>" . $website . "</a>";
                    } else {
                        $web = "<a href='http://" . $website . "' target='_blank'>" . $website . "</a>";
                    }
                } else
                    $web = '-';


                $service_type = get_post_meta(get_the_ID(), 'mm365_service_type', true);

                //$company_name = preg_replace("/#?[a-z0-9]+;/i"," ",wp_filter_nohtml_kses(get_the_title()));
                $company_name = wp_filter_nohtml_kses(get_the_title());
                $nestedData = array();

                if ($service_type == $meta or $meta == 'x') {
                    $nestedData[] = $this->get_certified_badge(get_the_ID(), true) . '<a href="' . site_url() . '/view-company?cid=' . get_the_ID() . '">' . $company_name . '</a>';
                    if ($council_id == '' or $is_admin_filtering == 'yes'):
                        $nestedData[] = $council_short_name;
                    endif;
                    $nestedData[] = $city . ", " . $state . ", " . $country;
                    $nestedData[] = "<div class='intable_span'>" . $contact_person . "</div><div class='intable_span'>" . $contact_phone . "</div><div class='intable_span'>" . $contact_email . "</div>";
                    $nestedData[] = $web;
                    $nestedData[] = $services_list;
                    $nestedData[] = $industries_list;
                } else
                    $wrong_found++;

                $data[] = $nestedData;

            }

            wp_reset_query();
            if ($wrong_found == 0) {
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

        } else {
            $json_data = array(
                "data" => array()
            );
            echo json_encode($json_data);
        }
        wp_die();

    }

    /**
     * @param string $period - week, month, year
     * @param string $company_type - buyer / seller
     * @param int $sa_council_filter 
     * 
     */
    function quick_download($period = 'week', $company_type = NULL, $sa_council_filter = NULL)
    {

        //IF sa_council_filter is present ovveride councilid
        if ($sa_council_filter != NULL) {
            $councilid = $sa_council_filter;
        } else
            $councilid = NULL;

        //Council filter for companies
        if ($councilid != '') {

            //Companies
            $council_filter_companies = array(
                'key' => 'mm365_company_council',
                'value' => $councilid,
                'compare' => '=',
            );
            $council_col_width = 0;
            $council_shortname = $this->get_council_info($councilid) . " - ";
        } else {
            $council_filter_companies = '';
            $council_col_width = 30;
            $council_shortname = '';
        }


        //All company reports
        switch ($company_type) {
            case 'buyer':
                $quickreports_args = array(
                    'posts_per_page' => -1,
                    // No limit
                    'post_type' => 'mm365_companies',
                    'post_status' => array('publish'),
                    'date_query' => array(
                        array('after' => '1 ' . $period . ' ago')
                    ),
                    'meta_query' => array(
                        array(
                            'key' => 'mm365_service_type',
                            'value' => 'buyer',
                            'compare' => '=',
                        ),
                        $council_filter_companies
                    )
                );
                $cap_state_col = 0;
                $cap_state_col_2 = 0;
                $col_service_location = 0;
                $file_name = "Report - " . $council_shortname . "Buyers Registered with in a " . $period;
                break;
            case 'seller':
                $quickreports_args = array(
                    'posts_per_page' => -1,
                    // No limit
                    'post_type' => 'mm365_companies',
                    'post_status' => array('publish'),
                    'date_query' => array(
                        array('after' => '1 ' . $period . ' ago')
                    ),
                    'meta_query' => array(
                        array(
                            'key' => 'mm365_service_type',
                            'value' => 'seller',
                            'compare' => '=',
                        ),
                        $council_filter_companies
                    )
                );
                $cap_state_col = 30;
                $cap_state_col_2 = 50;
                $col_service_location = 50;
                $file_name = "Report - " . $council_shortname . "Suppliers Registered with in a " . $period;
                break;

            default:
                $quickreports_args = array(
                    'posts_per_page' => -1,
                    // No limit
                    'post_type' => 'mm365_companies',
                    'post_status' => array('publish'),
                    'date_query' => array(
                        array('after' => '1 ' . $period . ' ago')
                    ),
                    'meta_query' => array(
                        $council_filter_companies
                    )

                );
                //date("m-d-Y", strtotime( date( "m-d-Y", strtotime( date("m-d-Y"))) . "-1 $period" ));
                $cap_state_col = 30;
                $cap_state_col_2 = 50;
                $col_service_location = 50;
                $file_name = "Report - " . $council_shortname . "Companies Registered with in a " . $period;
                break;
        }

        $quick_report_query = new \WP_Query($quickreports_args);
        $data = array();

        while ($quick_report_query->have_posts()):
            $quick_report_query->the_post();

            //services
            if (!empty(get_post_meta(get_the_ID(), 'mm365_services'))):
                //$services_list =  implode( ', ', get_post_meta( get_the_ID(), 'mm365_services' ));
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

            //Location of company
            $city = $this->get_cityname(get_post_meta(get_the_ID(), 'mm365_company_city', true), "");
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


            $mc = json_decode(get_post_meta(get_the_ID(), 'mm365_main_customers', true));
            if (!empty($mc)) {
                $current_customers = array();
                foreach ($mc as $key => $value) {
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


            //Certification
            $ar_certification = (get_post_meta(get_the_ID(), 'mm365_certifications'));
            if (!empty($ar_certification)) {
                $certifications = array();
                foreach ($ar_certification as $key => $value) {
                    array_push($certifications, $value);
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
            $naics_extract = (get_post_meta(get_the_ID(), 'mm365_naics_codes'));
            if (!empty($naics_extract)) {
                $naics = array();
                foreach ($naics_extract as $key => $value) {
                    array_push($naics, $value);
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
            $council_id = get_post_meta(get_the_ID(), 'mm365_company_council', true);
            $council_name = get_the_title($council_id);
            $council_short_name = get_post_meta($council_id, 'mm365_council_shortname', true);

            //Capability statements
            $capability_statements = "";
            $serviceable_locations = "-";

            if ($service_type == 'buyer' or $service_type == 'Buyer') {
                $capability_statements = "Not Applicable";
                $serviceable_locations = "Not Applicable";
            } else {
                if (get_post_meta(get_the_ID(), 'mm365_company_docs', true) != ''):
                    foreach (get_post_meta(get_the_ID(), 'mm365_company_docs', true) as $attachment_id => $attachment_url) {
                        $capability_statements .= '• ' . basename(get_attached_file($attachment_id)) . "\n\n";
                    }
                else:
                    $capability_statements = '-';
                endif;

                if (!empty(get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_countries'))):
                    $breaks = array("<br />", "<br>", "<br/>");
                    $countries = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_countries');
                    $states = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_states');
                    $locations = str_ireplace($breaks, "\r\n", $this->multi_countries_state_display($countries, $states));
                    $serviceable_locations = $locations;
                endif;
            }

            if ($website != '') {
                if (!filter_var($website, FILTER_VALIDATE_URL)) {
                    $website = '=HYPERLINK("http://' . $website . '", "' . $website . '")';
                } else {
                    $website = '=HYPERLINK("' . $website . '", "' . $website . '")';
                }
            } else
                $website = NULL;


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

            //Check if buyer
            if (get_post_meta(get_the_ID(), 'mm365_service_type', true) == 'seller') {
                $certified = (get_post_meta(get_the_ID(), 'mm365_certification_status', true) == 'verified') ? 'Yes' : 'No';
            } else
                $certified = 'NA';


            $company = array(
                $this->replace_html_in_companyname(get_the_title()),
                $council_short_name,
                $certified,
                html_entity_decode(strip_tags($description)),
                $services_list,
                $service_type,
                $serviceable_locations,
                $minority_code,
                $capability_statements,
                $looking_for_list,
                $industries_list,
                ($website ?? '-'),
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

            array_push($data, $company);
        endwhile;

        $writer = new XLSXWriter();

        $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 50, 'wrap_text' => true);
        $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 20);
        $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');

        $writer->writeSheetHeader(
            'Sheet1',
            array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string', '9' => 'string', '10' => 'string', '11' => 'string', '12' => 'string', '13' => 'string', '14' => 'string', '15' => 'string', '16' => 'string', '17' => 'string', '18' => 'string', '19' => 'string', '20' => 'string', '21' => 'string', '22' => 'string', '23' => 'string', '24' => 'string', '25' => 'string', '26' => 'string', '27' => 'string', '28' => 'string', '29' => 'string'),
            $col_options = ['widths' => [50, $council_col_width, 20, 40, 30, $cap_state_col, $col_service_location, $cap_state_col, $cap_state_col_2, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30], 'suppress_row' => true]
        );
        if ($councilid != '') {
            $writer->writeSheetRow('Sheet1', $rowdata = array($file_name . '. From ' . date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))) . "-1 " . $period)) . ' To ' . date('m/d/Y', time())), $styles1);
        } else {
            $writer->writeSheetRow('Sheet1', $rowdata = array($file_name, 'From ' . date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))) . "-1 " . $period)) . ' To ' . date('m/d/Y', time())), $styles1);
        }
        $writer->writeSheetRow(
            'Sheet1',
            array(
                'Company name',
                'Council',
                'Certified',
                'Description',
                'Company Services',
                'Service type',
                'Locations where services or products are available',
                'Minority classification',
                'Capability statement',
                'International Assistance from Council',
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

        header("Content-Type: application/json");
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


    }


    /**
     * 
     * 
     * 
     */
    function filtered_view()
    {


        //To check if council manager is accessing report
        $user = wp_get_current_user();

        $council_id = $this->get_userDC($user->ID);

        $request = $_GET;

        $from = $_REQUEST['from'];
        $to = $_REQUEST['to'];

        //date fall back
        if (empty($from)) {
            $from = '01/01/1975';
        }
        if (empty($to)) {
            $to = date("m/d/Y");
        }

        $service_type = $_REQUEST['service_type'];
        $company_country = $_REQUEST['company_country'];
        $company_state = $_REQUEST['company_state'];
        $company_city = $_REQUEST['company_city'];
        $search_employees = $_REQUEST['number_of_employees'];
        $search_companysize = $_REQUEST['company_size'];
        $minority_code = $_REQUEST['minority_category'];

        $toDate = date_parse_from_format("m/d/Y", $to);

        //Check if super admin is filtering the report for specific council
        $selected_council_id = $_REQUEST['council_id'];

        //Arguments append
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

        //Services
        $serv_array = array();
        $services = array();
        if (isset($_REQUEST['services'])) {
            $services = array_filter($_REQUEST['services'], array($this,"purge_empty"));
            if (in_array('other', $services))
                array_push($services, $_REQUEST['other_services']);
        }

        if (!empty($services)):
            $serv_array = array(
                'key' => 'mm365_services',
                'value' => $services,
                'compare' => 'IN',
            );
        endif;

        //Industries
        $indus_array = array();
        $indstry = array();
        if (isset($_GET['industry'])) {
            $indstry = array_filter($_GET['industry'], array($this,"purge_empty"));
            if (in_array('other', $indstry))
                array_push($indstry, $_GET['other_industry']);
        }
        if (count($indstry) > '0') {
            $indus_array = array(
                'key' => 'mm365_industry',
                'value' => $indstry,
                'compare' => 'IN',
            );
        }

        //Certifications
        $certification_array = array();
        $certification = array();
        if (isset($_REQUEST['certifications'])) {
            $certification = array_filter($_REQUEST['certifications'], array($this,"purge_empty"));
            if (in_array('other', $certification))
                array_push($certification, $_REQUEST['other_certification']);
        }
        if (!empty($certification)) {
            $certification_array = array(
                'key' => 'mm365_certifications',
                'value' => $certification,
                'compare' => 'IN',
            );
        }

        //Naics codes
        $naics_array = array();
        $naics = array();
        if (isset($_REQUEST['naics_codes'])) {
            $naics = $_REQUEST['naics_codes'];
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
        $intassi = array_filter($_REQUEST['international_assistance'], array($this,"purge_empty"));
        if (!empty($intassi)) {
            $int_assi_array = array(
                'key' => 'mm365_international_assistance',
                'value' => $intassi,
                'compare' => 'IN',
            );
        }


        if (is_numeric($company_city)) {
            $city_match = array('key' => 'mm365_company_city', 'value' => $company_city, 'compare' => '=');
        } else {
            $city_match = array();
        }
        if (is_numeric($company_state)) {
            $state_match = array('key' => 'mm365_company_state', 'value' => $company_state, 'compare' => '=');
        } else {
            $state_match = array();
        }
        if (is_numeric($company_country)) {
            $country_match = array('key' => 'mm365_company_country', 'value' => $company_country, 'compare' => '=');
        } else {
            $country_match = array();
        }

        //Employee Count
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

        //Size of company - dollar value
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

        if (!empty($service_type) and $service_type != 'all') {
            $service_type_match = array('key' => 'mm365_service_type', 'value' => $service_type, 'compare' => '=');
        } else {
            $service_type_match = array();
        }

        //+ Minority classification to filter
        //2.0 Onwards
        if ($minority_code != 'all' and !empty($minority_code)) {
            $minority_code_match = array('key' => 'mm365_minority_category', 'value' => $minority_code, 'compare' => '=');
        } else {
            $minority_code_match = array();
        }



        $args = array(
            'post_type' => 'mm365_companies',
            'post_status' => 'publish',
            'posts_per_page' => $request['length'],
            'offset' => $request['start'],
            'order' => 'DESC',
            'date_query' => array(
                'after' => $from,
                'before' => array(
                    'year' => $toDate['year'],
                    'month' => $toDate['month'],
                    'day' => $toDate['day'],
                ),
                'inclusive' => true,
            ),


        );


        if (isset($request['order'])):
            if ($request['order'][0]['column'] == 0 and $request['order'][0]['dir'] != '') {
                $args['orderby'] = array('title' => $request['order'][0]['dir']);
            }
        endif;

        if (!empty($request['search']['value'])) {

            $args['search_company_title'] = $request['search']['value'];
            $args['search_company_title_relation'] = 'OR';

            //Cities search
            $cities = $this->find_city($request['search']['value']);
            $cities_matched = array();
            foreach ($cities as $key => $value) {
                array_push($cities_matched, $value->id);
            }
            $look_cities = array();
            if (!empty($cities_matched)) {
                $look_cities = array(
                    'key' => 'mm365_company_city',
                    'value' => $cities_matched,
                    'compare' => 'IN'
                );
            }

            //states search
            $states = $this->find_state($request['search']['value']);
            $states_matched = array();
            foreach ($states as $key => $value) {
                array_push($states_matched, $value->id);
            }
            $look_states = array();
            if (!empty($states_matched)) {
                $look_states = array(
                    'key' => 'mm365_company_state',
                    'value' => $states_matched,
                    'compare' => 'IN'
                );
            }

            //countries search
            $countries = $this->find_country($request['search']['value']);
            $countries_matched = array();
            foreach ($countries as $key => $value) {
                array_push($countries_matched, $value->id);
            }
            $look_countries = array();
            if (!empty($countries_matched)) {
                $look_countries = array(
                    'key' => 'mm365_company_country',
                    'value' => $countries_matched,
                    'compare' => 'IN'
                );
            }



            $additional = array(
                'relation' => 'OR',
                $look_cities,
                $look_states,
                $look_countries,
                array(
                    'key' => 'mm365_services',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_industry',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_contact_person',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_company_phone',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_company_email',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_website',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_company_council',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'mm365_company_name',
                    'value' => sanitize_text_field($request['search']['value']),
                    'compare' => 'LIKE'
                )
            );



        } else
            $additional = array();

        //Council filter for council managers
        if ($council_id != '') {
            $council_filter = array(
                'key' => 'mm365_company_council',
                'value' => $council_id,
                'compare' => '=',
            );

        } else {
            $council_filter = array();
        }


        //Council filter for super admin (Filtering council from dropdown)
        if (isset($selected_council_id) and is_numeric($selected_council_id)) {
            $council_admin_filter = array(
                'key' => 'mm365_company_council',
                'value' => $selected_council_id,
                'compare' => '=',
            );
        } else
            $council_admin_filter = NULL;

        $args['meta_query'] = array(
            'relation' => 'AND',
            $council_admin_filter,
            $council_filter,
            $indus_array,
            $serv_array,
            $employee_match,
            $companysize_match,
            $city_match,
            $state_match,
            $country_match,
            $service_type_match,
            $certification_array,
            $naics_array,
            $int_assi_array,
            $minority_code_match,
            $additional
        );


        //Main query
        $companies_query = new \WP_Query($args);

        $totalData = $companies_query->found_posts;

        $wrong_found = 0;

        header("Content-Type: application/json");

        if ($companies_query->have_posts()) {
            while ($companies_query->have_posts()) {

                $companies_query->the_post();
                //services
                if (!empty(get_post_meta(get_the_ID(), 'mm365_services'))):
                    $services_list = '';
                    foreach (get_post_meta(get_the_ID(), 'mm365_services') as $service) {
                        if ($service != '')
                            $services_list .= "<div class='intable_span'>" . $service . '</div>';
                    }
                else:
                    $services_list = '-';
                endif;

                //Industries
                if (!empty((get_post_meta(get_the_ID(), 'mm365_industry')))):
                    foreach ((get_post_meta(get_the_ID(), 'mm365_industry')) as $key => $value) {
                        if ($value != '')
                            $industries[] = $value;
                    }
                    if (isset($industries)):
                        $industries_list = '';
                        foreach ($industries as $industry) {
                            $industries_list .= "<div class='intable_span'>" . $industry . '</div>';
                        }

                    endif;
                    $industries = array();
                else:
                    $industries_list = '-';
                endif;

                //Location
                $city = $this->get_cityname(get_post_meta(get_the_ID(), 'mm365_company_city', true), "");
                $state = $this->get_statename(get_post_meta(get_the_ID(), 'mm365_company_state', true));
                $country = $this->get_countryname(get_post_meta(get_the_ID(), 'mm365_company_country', true));
                //Contact
                $contact_person = get_post_meta(get_the_ID(), 'mm365_contact_person', true);
                $contact_phone = get_post_meta(get_the_ID(), 'mm365_company_phone', true);
                $contact_email = get_post_meta(get_the_ID(), 'mm365_company_email', true);
                $website = strtolower(get_post_meta(get_the_ID(), 'mm365_website', true));

                //Get Council Details
                $cmp_council_id = get_post_meta(get_the_ID(), 'mm365_company_council', true);
                $council_name = get_the_title($cmp_council_id);
                $council_short_name = get_post_meta($cmp_council_id, 'mm365_council_shortname', true);

                if($website != ''){

                    if (filter_var($website, FILTER_VALIDATE_URL)) {
                        $web = "<a href='" . $website . "' target='_blank'>" . ($website) . "</a>";
                    } else {
                        $web = "<a href='http://" . ($website) . "' target='_blank'>" . ($website) . "</a>";
                    }
                } 

                $service_type = get_post_meta(get_the_ID(), 'mm365_service_type', true);
                $company_name = wp_filter_nohtml_kses(get_the_title());
                $nestedData = array();

                $nestedData[] = $this->get_certified_badge(get_the_ID(), true) . '<a href="' . site_url() . '/view-company?cid=' . get_the_ID() . '">' . $company_name . '</a>';
                if ($council_id == ''):
                    $nestedData[] = $council_short_name;
                endif;
                $nestedData[] = $city . ", " . $state . ", " . $country;
                $nestedData[] = "<div class='intable_span'>" . $contact_person . "</div><div class='intable_span'>" . $contact_phone . "</div><div class='intable_span'>" . $contact_email . "</div>";
                $nestedData[] = $web ?: '-';
                $nestedData[] = $services_list;
                $nestedData[] = $industries_list;

                $data[] = $nestedData;

            }
            wp_reset_query();

            if ($wrong_found == 0) {
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
    function filtered_download()
    {
        $user = wp_get_current_user();
        $council_id = $this->get_userDC($user->ID);
      
        if ($council_id != '') {
          $counil_col_width = 0;
        } else {
          $counil_col_width = 35;
        }

        //Parameters
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];
        $service_type = $_POST['service_type'];
        $company_city = (isset($_POST['company_city'])) ? $_POST['company_city'] : NULL;
        $company_state = (isset($_POST['company_state'])) ? $_POST['company_state'] : NULL;
        $company_country = (isset($_POST['company_country'])) ? $_POST['company_country'] : NULL;
        $search_employees = $_POST['number_of_employees'];
        $search_companysize = $_POST['size_of_company'];
        $search_council = $_POST['council_filter'];
        $minority_code = $_POST['minority_category'];
      
        //
        if (empty($from_date)) {
          $from_date = '01/01/1975';
        }
        if (empty($to_date)) {
          $to_date = date("m/d/Y");
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
      
        if ($search_companysize == '<$100,000' or $search_companysize == '< $100,000'):
          $cs_search = array(
            "$100,000 - $500,000",
            "$500,000 - $1M",
            "$1M - $5M",
            "$5M - $50M",
            "$50M - $200M",
            "$200M - $500M",
            "$500M - $1B",
            "$1B+"
          );
      
          $cs_compare = "NOT IN";
        else:
          $cs_search = $search_companysize;
          $cs_compare = "=";
        endif;
      
      
      
        $serv_array = array();
        $services = array();
        if (isset($_POST['services'])) {
          $services = $_POST['services'];
          if (in_array('other', $services))
            array_push($services, $_POST['other_services']);
        }
      
        if (!empty($services)):
          $serv_array = array(
            'key' => 'mm365_services',
            'value' => $services,
            'compare' => 'IN',
          );
        endif;
      
        $indus_array = array();
        $indstry = array();
        if (isset($_POST['industry'])) {
          $indstry = $_POST['industry'];
          if (in_array('other', $indstry))
            array_push($indstry, $_POST['other_industry']);
        }
        if (!empty($indstry)) {
          $indus_array = array(
            'key' => 'mm365_industry',
            'value' => $indstry,
            'compare' => 'IN',
          );
        }
      
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
        if (!empty($_POST['international_assistance'])) {
          $int_assi_array = array(
            'key' => 'mm365_international_assistance',
            'value' => $_POST['international_assistance'],
            'compare' => 'IN',
          );
        }
      
        if (!empty($search_council)) {
          $search_council_para = array(
            'key' => 'mm365_company_council',
            'value' => $search_council,
            'compare' => '=',
          );
        } else
          $search_council_para = array();
      
      
        if (is_numeric($company_city)) {
          $city_match = array('key' => 'mm365_company_city', 'value' => $company_city, 'compare' => '=');
        } else {
          $city_match = array();
        }
        if (is_numeric($company_state)) {
          $state_match = array('key' => 'mm365_company_state', 'value' => $company_state, 'compare' => '=');
        } else {
          $state_match = array();
        }
        if (is_numeric($company_country)) {
          $country_match = array('key' => 'mm365_company_country', 'value' => $company_country, 'compare' => '=');
        } else {
          $country_match = array();
        }
      
        if ($ec_search != '' or !empty($ec_search)) {
          $employee_match = array(
            'key' => 'mm365_number_of_employees',
            'value' => $ec_search,
            'compare' => $ec_compare
          );
        } else {
          $employee_match = array();
        }
      
        if (!empty($cs_search)) {
          $companysize_match = array(
            'key' => 'mm365_size_of_company',
            'value' => $cs_search,
            'compare' => $cs_compare
          );
        } else {
          $companysize_match = array();
        }
        if (!empty($service_type) and $service_type != 'all') {
          $service_type_match = array('key' => 'mm365_service_type', 'value' => $service_type, 'compare' => '=');
        } else {
          $service_type_match = array();
        }
      
        //+ Minority classification to filter
        //2.0 Onwards
        if ($minority_code != 'all' and !empty($minority_code)) {
          $minority_code_match = array('key' => 'mm365_minority_category', 'value' => $minority_code, 'compare' => '=');
        } else {
          $minority_code_match = array();
        }
      
      
        $toDate = date_parse_from_format("m/d/Y", $to_date);
      
        //Get data
        $report_query_args = array(
          'posts_per_page' => -1,
          // No limit
          //'fields'         => 'ids', // Reduce memory footprint
          'post_type' => 'mm365_companies',
          'post_status' => array('publish'),
          'date_query' => array(
            'after' => $from_date,
            'before' => array(
              'year' => $toDate['year'],
              'month' => $toDate['month'],
              'day' => $toDate['day'],
            ),
            'inclusive' => true,
          ),
          'meta_query' => array(
      
            'relation' => 'AND',
            $indus_array,
            $serv_array,
            $employee_match,
            $companysize_match,
            $city_match,
            $state_match,
            $country_match,
            $service_type_match,
            $certification_array,
            $naics_array,
            $int_assi_array,
            $search_council_para,
            $minority_code_match
          )
      
        );
      
      
        $report_query = new \WP_Query($report_query_args);
        $found_results = $report_query->found_posts;
        //Check result count
        if ($found_results > 0){
          $data = array();
      
          if ($service_type == 'buyer' or $service_type == 'Buyer'):
            $cap_state_col = 0;
            $cap_state_col_2 = 0;
          else:
            $cap_state_col = 30;
            $cap_state_col_2 = 50;
          endif;
      
      
          while ($report_query->have_posts()):
            $report_query->the_post();
      
            //services
            $ar_services_list = (get_post_meta(get_the_ID(), 'mm365_services'));
            if (!empty($ar_services_list)) {
              $services = array();
              foreach ($ar_services_list as $key => $value) {
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
            } else {
              $services_list = '-';
            }
      
            //Industries
            if (!empty((get_post_meta(get_the_ID(), 'mm365_industry')))):
              foreach ((get_post_meta(get_the_ID(), 'mm365_industry')) as $key => $value) {
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
            $ar_certification = get_post_meta(get_the_ID(), 'mm365_certifications');
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
      
            //Curent customers
            $mc = json_decode(get_post_meta(get_the_ID(), 'mm365_main_customers', true));
            if (!empty($mc)):
              $current_customers = array();
              foreach ($mc as $key => $value) {
                if ($value != '') {
                  array_push($current_customers, $value);
                }
              }
              //$customers   =  implode( ', ', $current_customers);
              $customers = '';
              foreach ($current_customers as $customer) {
                $customers .= "\n" . '• ' . $customer . '';
              }
            else:
              $customers = '-';
            endif;
      
      
      
            //NAICS Codes
            $naics = array();
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
      
            $description = get_post_meta(get_the_ID(), 'mm365_company_description', true);
            $contact_person = get_post_meta(get_the_ID(), 'mm365_contact_person', true);
            $contact_address = get_post_meta(get_the_ID(), 'mm365_company_address', true);
            $contact_phone = get_post_meta(get_the_ID(), 'mm365_company_phone', true);
            $contact_email = get_post_meta(get_the_ID(), 'mm365_company_email', true);
      
            $alt_phone = get_post_meta(get_the_ID(), 'mm365_alt_phone', true);
            $alt_email = get_post_meta(get_the_ID(), 'mm365_alt_email', true);
            $alt_contact = get_post_meta(get_the_ID(), 'mm365_alt_contact_person', true);
      
            $website = get_post_meta(get_the_ID(), 'mm365_website', true);
            $zip_code = get_post_meta(get_the_ID(), 'mm365_zip_code', true);
            $minority_category = $this->expand_minoritycode(get_post_meta(get_the_ID(), 'mm365_minority_category', true));
      
            //Get Council Details
            $cmp_council_id = get_post_meta(get_the_ID(), 'mm365_company_council', true);
            $council_name = get_the_title($cmp_council_id);
            $council_short_name = get_post_meta($cmp_council_id, 'mm365_council_shortname', true);
      
      
            //Type
            $cmp_service_type = $this->get_company_service_type(get_post_meta(get_the_ID(), 'mm365_service_type', true));
      
            //Capability statements
            $capability_statements = "";
            $serviceable_locations = "-";
            if ($cmp_service_type == 'buyer' or $cmp_service_type == 'Buyer'){
              $capability_statements = "Not Applicable";
              $serviceable_locations = "Not Applicable";
            }else{
              if (get_post_meta(get_the_ID(), 'mm365_company_docs', true) != ''):
                foreach (get_post_meta(get_the_ID(), 'mm365_company_docs', true) as $attachment_id => $attachment_url) {
                  $capability_statements .= '• ' . basename(get_attached_file($attachment_id)) . "\n\n";
                }
              else:
                $capability_statements = "-";
              endif;
      
              if (!empty(get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_countries'))):
                $breaks = array("<br />", "<br>", "<br/>");
                //$locations = str_ireplace($breaks, "\r\n", $cmp_addons->service_location_display(get_the_ID(), 2));
                $countries = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_countries');
                $states = get_post_meta(get_the_ID(), 'mm365_cmp_serviceable_states');
                $locations = str_ireplace($breaks, "\r\n", $this->multi_countries_state_display($countries, $states));

                $serviceable_locations = $locations;
              endif;
            }
      
            //Int assi looking for
            if (!empty((get_post_meta(get_the_ID(), 'mm365_international_assistance')))){
              foreach ((get_post_meta(get_the_ID(), 'mm365_international_assistance')) as $key => $value) {
                $looking_for[] = $value;
              }
              if (isset($looking_for)):
                //$minority_codes_list = implode( ', ', $mincode );
                $looking_for_list = '';
                foreach ($looking_for as $ldata) {
                  $looking_for_list .= "\n" . '• ' . $ldata . '';
                }
              endif;
              $looking_for = array();
            }else{
              $looking_for_list = '-';
            }
      
      
            if ($website != '') {
              if (!filter_var($website, FILTER_VALIDATE_URL)) {
                $website = '=HYPERLINK("http://' . $website . '", "' . $website . '")';
              } else {
                $website = '=HYPERLINK("' . $website . '", "' . $website . '")';
              }
            }
      
            //Check if buyer
            if (get_post_meta(get_the_ID(), 'mm365_service_type', true) == 'seller') {
              $certified = (get_post_meta(get_the_ID(), 'mm365_certification_status', true) == 'verified') ? 'Yes' : 'No';
            } else
              $certified = 'NA';
      
            $company = array(
              $this->replace_html_in_companyname(get_the_title()),
              $council_short_name,
              $certified,
              html_entity_decode(strip_tags($description)),
              $services_list,
              $cmp_service_type,
              $serviceable_locations,
              $minority_category,
              $capability_statements,
              $looking_for_list,
              $industries_list,
              ($website ?: '-'),
              $contact_person,
              $contact_address,
              $contact_phone,
              $contact_email,
              $city,
              $state,
              $country,
              $zip_code,
              ($alt_contact ?: '-'),
              ($alt_phone ?: '-'),
              ($alt_email ?: '-'),
              $ec ?: '-',
              ($company_size ?: '-'),
              ($customers ?: '-'),
              $certifications_list,
              $naics_list,
              get_post_time("m/d/Y h:i A"),
              get_the_modified_time("m/d/Y h:i A"),
              get_the_ID()
            );
            array_push($data, $company);
          endwhile;
      
      
          $writer = new XLSXWriter();
      
          $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 50, 'wrap_text' => true);
          $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 20);
          $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');
      
          $writer->writeSheetHeader(
            'Sheet1',
            array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string', '9' => 'string', '10' => 'string', '11' => 'string', '12' => 'string', '13' => 'string', '14' => 'string', '15' => 'string', '16' => 'string', '17' => 'string', '18' => 'string', '19' => 'string', '20' => 'string', '21' => 'string', '22' => 'string', '23' => 'string', '24' => 'string', '25' => 'string', '26' => 'string', '27' => 'string', '28' => 'string', '29' => 'string'),
            $col_options = ['widths' => [50, $counil_col_width, 20, 40, 30, $cap_state_col, $cap_state_col_2, $cap_state_col, $cap_state_col_2, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 10], 'suppress_row' => true]
          );
      
          if ($from_date != '01/01/1975'):
            $xls_heading = "Company Registration Report \n" . 'From ' . date_format(date_create($from_date), 'm/d/Y') . " " . 'To ' . date_format(date_create($to_date), 'm/d/Y');
          else:
            $xls_heading = "Company Registration Report";
          endif;
      
          $writer->writeSheetRow('Sheet1', array($xls_heading), $styles1);
      
          $writer->writeSheetRow(
            'Sheet1',
            $rowdata = array(
              'Company name',
              'Council',
              'Certified',
              'Description',
              'Company Services',
              'Service type',
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
              'Updated date',
              'id'
            ),
            $styles2
          );
          //$writer->writeSheet($data);
          foreach ($data as $dat) {
            $writer->writeSheetRow('Sheet1', $dat, $styles3);
          }
          //print_r($data);
          //$writer->writeSheet($data);
          $file = 'Company Registration Report - ' . time() . '.xlsx';
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
      
          wp_reset_postdata();
          die();
          //if no results
        }else{
      
          if (!isset($_POST['companyreportnonce']) || !wp_verify_nonce($_POST['companyreportnonce'], 'mm365-report-company')) {
            setcookie('report_generate_status_cmp', 'err', time() + 3600, "/","");
            wp_redirect(site_url() . '/reports-company-registration');
          } else {
            setcookie('report_generate_status_cmp', 'err', time() + 3600, "/","");
            wp_redirect(site_url() . '/company-report-council-manager');
          }
      
          die();
        }

    }
}
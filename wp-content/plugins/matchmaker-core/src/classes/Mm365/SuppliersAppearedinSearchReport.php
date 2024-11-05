<?php

namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Report shows the count of MBEs(Suppliers) appeared in match results
 * Company appeared in maximum number of match requests result set
 * will be on top of the list
 * 
 * To get the report we are collecting result sets from all match results
 * and do array opertaions to count the number of appearances of each mbes
 * matched
 * 
 * MBEs - Supplier companies
 */
class SuppliersAppearedinSearchReport{

	use CertificateAddon;

	public function __construct(){

        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        //Listing
        add_action( 'wp_ajax_mbe_occurrence_listing', array( $this, 'mbe_occurrence_listing' ) );

        //Company appeared matches
        add_action( 'wp_ajax_mbe_occurrence_details', array( $this, 'mbe_occurrence_details') );

		//Filters
		add_filter('mm365_download_suppliers_appeared_in_search_report', array( $this, 'download_occurrence_report'));

    }


	/**
	 * 
	 * 
	 */
	function assets(){
        if ( wp_register_script( 'mm365_mbe_occurrence',plugins_url('matchmaker-core/assets/mm365_mbe_occurrence.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_mbe_occurrence' );
            wp_localize_script( 'mm365_mbe_occurrence', 'mbeOccurrenceAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("mbeOccurrence_ajax_nonce")
            ) );
          
        }    
    }


	/**
	 * 
	 * 
	 * 
	 * 
	 */

	 public function occurance_count(){
        
        //Experimental
        global $wpdb;
        $sql  =  "SELECT ".$wpdb->prefix."postmeta.meta_value AS matched_data FROM `".$wpdb->prefix."postmeta` INNER JOIN ".$wpdb->prefix."posts ON ".$wpdb->prefix."postmeta.post_id = ".$wpdb->prefix."posts.ID WHERE ".$wpdb->prefix."postmeta.`meta_key` = 'mm365_matched_companies' AND ".$wpdb->prefix."postmeta.`meta_value` != '' AND ".$wpdb->prefix."posts.post_status = 'publish'";
        //Company description
        $matchresults_jsons  = $wpdb->get_results($sql);

        $match_results = array();
        foreach ($matchresults_jsons as  $value) {
            $matched =  maybe_unserialize(maybe_unserialize($value->matched_data));
          
            if(is_array($matched)):
          
                foreach ($matched as $key => $subArr) { 
                    unset($matched[$key][1]);      
                }
                //Push to master array with flattening of array to single dimension
                array_push($match_results, call_user_func_array('array_merge', $matched));
          
            endif;
          }
          
        $master_MatchedCompanies = call_user_func_array('array_merge', $match_results);
          
        $mbes = array_count_values($master_MatchedCompanies);
        
        arsort($mbes);

        return $mbes;
    }


    /**
     * 
     * LISTING
     * Shown in 'mbe-occurrence-report' through data tables
     */


    public function mbe_occurrence_listing(){

        //Experiment ends
        header("Content-Type: application/json");
        $request= $_POST;

        $columns = array(
            0 => 'company_name',
            1 => 'number_of_appearences',
            2 => 'contact_person',
            3 => 'email',
            4 => 'phone'
            
        );

        $mbes = $this->occurance_count();

        $company_ids = array();
        foreach ($mbes as $key => $value) {
            array_push($company_ids, $key);
        }

        $args = array(
                    'post_type'   => 'mm365_companies',
                    'post_status' => 'publish',
                    'post__in'    => $company_ids,
                    'orderby'     => 'post__in',
                    'posts_per_page' => $request['length'],
                    'offset' => $request['start'],
                );

        

        //Search input

        if( !empty($request['search']['value']) ) {

            $args['meta_query'] = array(
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'mm365_contact_person',
                        'value'   => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => 'mm365_company_email',
                        'value'   => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ), 
                    array(
                        'key'     => 'mm365_company_phone',
                        'value'   => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => 'mm365_company_name',
                        'value'   => sanitize_text_field($request['search']['value']),
                        'compare' => 'LIKE'
                    )             
                )
                               
            );
        }

        //Run query
        $get_mbes = new \WP_Query($args);

        //For paginating
        $totalData = $get_mbes->found_posts;
		$data = [];
        if( $get_mbes->have_posts() ) :
            while(  $get_mbes->have_posts() ) :  $get_mbes->the_post();

                    $nestedData = array();

                    $nestedData[] =  $this->get_certified_badge(get_the_ID(), true)."<a target='_blank' href='".site_url('view-company')."?cid=".get_the_ID()."'>".esc_html(get_the_title( get_the_ID() ))."</a>";
                    $nestedData[] = "<div class='text-center'><a target='_blank' href='".site_url('appeared-matches')."?cid=".get_the_ID()."'>".esc_html($mbes[get_the_ID()])."</a></div>";
                    $nestedData[] = esc_html(get_post_meta( get_the_ID(), 'mm365_contact_person', true ));
                    $nestedData[] = esc_html(get_post_meta( get_the_ID(), 'mm365_company_email', true ));
                    $nestedData[] = esc_html(get_post_meta( get_the_ID(), 'mm365_company_phone', true ));
                    $data[] = $nestedData;

            endwhile;

            $json_data = array(
                "draw" => intval($request['draw']),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalData),
                "data" => $data
            );
        else:
            $json_data = array(
                "data" => $data
            );
        endif;

        wp_reset_query();

        echo json_encode($json_data);

        wp_die();

    }


    /**
     * 
     * Returns the ids of match requests that a specific 
     * company is appeared as match
     * 
     */

     public function company_appeared_matchrequests($company_id){

            //Get 'matched_companies' from all match requests
            $match_requests = new \WP_Query( array(
                'post_type'   => 'mm365_matchrequests',
                'post_status' => 'publish',
                'posts_per_page'   => -1,
                'fields'      => 'ids',
            ));

            $match_results = array();
            if( $match_requests->have_posts() ) :
                while( $match_requests->have_posts() ) : $match_requests->the_post();
                    $matched =  maybe_unserialize(get_post_meta( get_the_ID(), 'mm365_matched_companies', true));
                    //removes approval status element from array Sample as in json:{company_id:approval_status}
                    if(is_array($matched)):
                        foreach ($matched as $key => $subArr) { 
                            unset($matched[$key][1]);      
                        }
                        $match_results[get_the_ID()] = call_user_func_array('array_merge', $matched);
                    endif;
                endwhile;
            endif;

            //Find all match request ids
            foreach($match_results as $key => $value){
                if(in_array($company_id,$value)){
                    $match_request_ids[] = $key;
                }
            }

            return $match_request_ids;

     }

    /**
     * 
     * List the match requests which a company has appeared 
     * as result
     * 
     */

     public function mbe_occurrence_details(){

        //nonce check
        $nonce    = $_POST['nonce'];
        if (!wp_verify_nonce( $nonce, 'mbeOccurrence_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        header("Content-Type: application/json");
        $request= $_POST;

        //Company id
        $company_id = $_POST['cmp_id'];
        $match_ids  = $this->company_appeared_matchrequests($company_id);


        //
        $columns = array(
            0 => 'keywords',
            1 => 'requester',
            2 => 'contact_person',
            3 => 'email',
            4 => 'phone'
            
        );



        $args = array(
                    'post_type'   => 'mm365_matchrequests',
                    'post_status' => 'publish',
                    'post__in'    => $match_ids,
                    'orderby'     => 'post__in',
                    'posts_per_page' => $request['length'],
                    'offset' => $request['start'],
                );

        

        //Search input

        if( !empty($request['search']['value']) ) {

            $args['meta_query'] = array(
                array(
                    'relation' => 'OR',
                    // array(
                    //     'key'     => 'mm365_contact_person',
                    //     'value'   => sanitize_text_field($request['search']['value']),
                    //     'compare' => 'LIKE'
                    // ),
                    // array(
                    //     'key'     => 'mm365_company_email',
                    //     'value'   => sanitize_text_field($request['search']['value']),
                    //     'compare' => 'LIKE'
                    // ), 
                    // array(
                    //     'key'     => 'mm365_company_phone',
                    //     'value'   => sanitize_text_field($request['search']['value']),
                    //     'compare' => 'LIKE'
                    // ),
                    // array(
                    //     'key'     => 'mm365_company_name',
                    //     'value'   => sanitize_text_field($request['search']['value']),
                    //     'compare' => 'LIKE'
                    // )             
                )
                               
            );
        }

        //Run query
        $get_matchrequests = new \WP_Query($args);

        //For paginating
        $totalData = $get_matchrequests->found_posts;

        if(  $get_matchrequests->have_posts() ) :
            while(  $get_matchrequests->have_posts() ) :  $get_matchrequests->the_post();

                $company_id          = get_post_meta( get_the_ID(), 'mm365_requester_company_id', true ); 
                if($company_id!=''): 
					$company_name  = get_the_title($company_id); 
			    else: 
					$company_name  = ''; 
		        endif;

                $council_id          = get_post_meta(get_the_ID(), 'mm365_requester_company_council', true ); 
                $council             = get_post_meta( $council_id, 'mm365_council_shortname', true ); 
                $last_updated_byuser = get_post_meta( get_the_ID(), 'mm365_matched_companies_last_updated', true ); 
                $status              = get_post_meta( get_the_ID(), 'mm365_matchrequest_status', true );

                $nestedData = array();
                $nestedData[] = get_post_meta( get_the_ID(), 'mm365_services_details', true );
                $nestedData[] = "<a target='_blank' href='".site_url('view-company')."?cid=".$company_id."'>".esc_html($company_name)."</a>";
                $nestedData[] = $council;
                $nestedData[] = $last_updated_byuser;
                $nestedData[] = get_post_meta( get_the_ID(), 'mm365_location_for_search', true );

                if($status != 'nomatch'){ 
                        $nestedData[] = '<a href="'.site_url().'/view-match-request-details?mr_id='.get_the_ID().'">View Details</a><br/>
                                     <a href="'.site_url().'/admin-match-request-manage?mr_id='.get_the_ID().'">View Match</a>'; 
                } else {
                        $nestedData[] = '<a href="'.site_url().'/view-match-request-details?mr_id='.get_the_ID().'">View Details</a><br/>
                                         <span class="text-disabled">View Match</span>';
                }


                $data[] = $nestedData;

            endwhile;

        endif;

        wp_reset_query();

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalData),
            "data" => $data
        );

        echo json_encode($json_data);

        wp_die();

     }


     /**
      * Download XLS
      */
      function download_occurrence_report(){


        $mbes = $this->occurance_count();
        $company_ids = array();
        foreach ($mbes as $key => $value) {
            array_push($company_ids, $key);
        }


        //Array for XLS
        $data = array();

        $args = array(
            'post_type'   => 'mm365_companies',
            'post_status' => 'publish',
            'post__in'    => $company_ids,
            'orderby'     => 'post__in',
            'posts_per_page' => -1
        );

        $mbe_list_query = new \WP_Query( $args );
        while ( $mbe_list_query->have_posts() ) : $mbe_list_query->the_post();

            $mbe_details = array(
                get_the_title( get_the_ID() ),
                esc_html($mbes[get_the_ID()]),
                esc_html(get_post_meta( get_the_ID(), 'mm365_contact_person', true )),
                esc_html(get_post_meta( get_the_ID(), 'mm365_company_email', true )),
                esc_html(get_post_meta( get_the_ID(), 'mm365_company_phone', true ))
            );

        //Array to write    
        array_push($data,$mbe_details);
        endwhile;



        $writer = new XLSXWriter();

        $file_name = "Report - MBEs Occurrence Count";
                           
        $styles1 = array( 'font'=>'Arial','font-size'=>10,'font-style'=>'bold', 'fill'=>'#ffc00', 'color'=>'#000','halign'=>'center','valign'=>'center','height'=>30,'wrap_text'=>true);
        $styles2 = array( 'font'=>'Arial','font-size'=>10,'font-style'=>'bold', 'fill'=>'#356ab3', 'color'=>'#fff','halign'=>'center','valign'=>'center','height'=>30,'wrap_text'=>true);
        $styles3 = array( 'border'=>'left,right,top,bottom','border-color'=>'#000','border-style'=>'thin','wrap_text'=>true,'valign'=>'top');
        $styles4 = array( ['border'=>'left,right,top,bottom','border-color'=>'#000','border-style'=>'thin','font'=>'Arial','font-size'=>10, 'height'=>30,'font-style'=>'bold', 'fill'=>'#356ab3', 'color'=>'#fff','halign'=>'left','valign'=>'top','wrap_text'=>true],
        ['border'=>'left,right,top,bottom','border-color'=>'#000','border-style'=>'thin','font'=>'Arial','font-size'=>10, 'fill'=>'#fff', 'color'=>'#000','halign'=>'left','valign'=>'top','wrap_text'=>true]);

        $writer->writeSheetHeader('Sheet1', array('1'=>'string','2'=>'string','3'=>'string','4'=>'string','5'=>'string','6'=>'string','7'=>'string','8'=>'string','9'=>'string','10'=>'string','11'=>'string','12'=>'string','13'=>'string','14'=>'string','15'=>'string','16'=>'string','17'=>'string','18'=>'string','19'=>'string','20'=>'string','21'=>'string','22'=>'string','23'=>'string','24'=>'string','25'=>'string','26'=>'string','27'=>'string','28'=>'string','29'=>'string','30'=>'string'), 
        $col_options = ['widths'=>[50,30,30,30,40],'suppress_row'=>true] );
        
        $writer->writeSheetRow('Sheet1', array("MBE Occurrence Report",""), $styles1 );
        $writer->writeSheetRow('Sheet1', array("Data exported till ",date("m/d/Y h:i A",time())), $styles1 );

        $writer->writeSheetRow('Sheet1', 
        array(
            'Company name',
            'Number of appearances',
            'Contact Person',
            'Email',
            'Phone'
        ), $styles2 );

        foreach($data as $dat){
            $writer->writeSheetRow('Sheet1', $dat,$styles3);
        }
        
        $file = $file_name.'.xlsx';
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


      }


}
<?php
/**
 * The initation loader for Matchmaker365 Data Fixer, and the main plugin file.

 * Plugin Name:  Matchmaker365 Data Fixer
 * Plugin URI:   https://v2soft.com
 * Description:  This plugin handles core logics and all major fuctionalities for Matchmaker365 Portal
 * Author:       V2Soft team
 * Author URI:   https://v2soft.com
 * Version:      1.0.0
 *
 * Text Domain:  matchmakerfixer
 * Domain Path:  languages
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . 'wp-includes/pluggable.php' );

if(!function_exists('is_plugin_active')){
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
/**---------------------------------------------
  * Fix data in match requests
  -----------------------------------------------
  */

  //read all match Requests

  //find all fields requires repair

  //Delete and re write
// function fixmydata_matchrequests(){

//           $args = array(  
//             'post_type' => 'mm365_matchrequests',
//             'posts_per_page' => -1, 
//             'orderby' => 'date', 
//         );
//         $loop = new WP_Query( $args );  
//         while ( $loop->have_posts() ) : $loop->the_post(); 

//         //mm365_certifications
//         //mm365_mr_mbe_category
//         //mm365_services_industry
//         //mm365_services_looking_for
//         //mm365_naics_codes
//         $post_id = get_the_ID();
//         //Loop and save NAICS
//         $new_naics = json_decode(get_post_meta( get_the_ID(), 'mm365_naics_codes', true ));
//         delete_post_meta($post_id, 'mm365_naics_codes');
//         foreach ($new_naics as $naic) {
//           add_post_meta($post_id, 'mm365_naics_codes', $naic );
//         }

//         $new_certification = json_decode(get_post_meta( get_the_ID(), 'mm365_certifications', true ));
//         delete_post_meta( $post_id, 'mm365_certifications');
//         foreach ($new_certification as $data) {
//           add_post_meta( $post_id, 'mm365_certifications', $data );
//         }

//         $new_services = json_decode(get_post_meta( get_the_ID(), 'mm365_services_looking_for', true ));
//         delete_post_meta( $post_id, 'mm365_services_looking_for');
//         foreach ($new_services as $data) {
//           add_post_meta( $post_id, 'mm365_services_looking_for', $data );
//         }  

//         $new_industry = json_decode(get_post_meta( get_the_ID(), 'mm365_services_industry', true ));
//         delete_post_meta( $post_id, 'mm365_services_industry');
//         foreach ($new_industry as $data) {
//           add_post_meta( $post_id, 'mm365_services_industry', $data );
//         } 

//         $new_mbecat = json_decode(get_post_meta( get_the_ID(), 'mm365_mr_mbe_category', true ));
//         delete_post_meta( $post_id, 'mm365_mr_mbe_category');
//         foreach ($new_mbecat as $data) {
//           add_post_meta( $post_id, 'mm365_mr_mbe_category', $data );
//         }   


//         endwhile;
//         wp_reset_postdata();
// }





// function fixmydata_companies(){

//   $args = array(  
//     'post_type' => 'mm365_companies',
//     'posts_per_page' => -1, 
//     'orderby' => 'date', 
// );
// $loop = new WP_Query( $args );  
// while ( $loop->have_posts() ) : $loop->the_post(); 


// $post_id = get_the_ID();
// //Loop and save NAICS


//           //Loop and save NAICS
//           $new_naics = json_decode(get_post_meta( get_the_ID(), 'mm365_naics_codes', true ));
//           delete_post_meta($post_id, 'mm365_naics_codes');
//           foreach ($new_naics as $naic) {
//             add_post_meta($post_id, 'mm365_naics_codes', $naic );
//           }
//           //certification
//           $new_certification = json_decode(get_post_meta( get_the_ID(), 'mm365_certifications', true ));
//           delete_post_meta( $post_id, 'mm365_certifications');
//           foreach ($new_certification as $data) {
//             add_post_meta( $post_id, 'mm365_certifications', $data );
//           }  

//           //Services
//           $new_services = json_decode(get_post_meta( get_the_ID(), 'mm365_services', true ));
//           delete_post_meta( $post_id, 'mm365_services');
//           foreach ($new_services as $data) {
//             add_post_meta( $post_id, 'mm365_services', $data );
//           }  

//           //Industry
//           $new_industry = json_decode(get_post_meta( get_the_ID(), 'mm365_industry', true ));
//           delete_post_meta( $post_id, 'mm365_industry');
//           foreach ($new_industry as $data) {
//             add_post_meta( $post_id, 'mm365_industry', $data );
//           }  





// endwhile;
// wp_reset_postdata();
// }


// function fixmydata_matchrequests_dateiso(){

//   $args = array(  
//     'post_type' => 'mm365_matchrequests',
//     'posts_per_page' => -1, 
//     'orderby' => 'date', 
// );
// $loop = new WP_Query( $args );  
// while ( $loop->have_posts() ) : $loop->the_post(); 
//   $lm_date     = get_post_meta( get_the_ID(), 'mm365_matched_companies_last_updated', true );
//   $lm_date_iso = date('Y-m-d', strtotime($lm_date));
//   update_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated_isodate',$lm_date_iso);
// endwhile;
// wp_reset_postdata();
// }



// function add_council_to_mmsdc_companies(){
//     $args = array(  
//         'post_type' => 'mm365_companies',
//         'posts_per_page' => -1, 
//         'orderby' => 'date', 
//     );
//     $loop = new WP_Query( $args );  
//     while ( $loop->have_posts() ) : $loop->the_post(); 
//       update_post_meta(get_the_ID(), 'mm365_company_council','6928');
//     endwhile;
//     wp_reset_postdata();
// }


// function add_council_to_mmsdc_matchrequests(){
//     $args = array(  
//         'post_type' => 'mm365_matchrequests',
//         'posts_per_page' => -1, 
//         'orderby' => 'date', 
//     );
//     $loop = new WP_Query( $args );  
//     while ( $loop->have_posts() ) : $loop->the_post(); 
//       update_post_meta(get_the_ID(), 'mm365_requester_company_council','6928');
//     endwhile;
//     wp_reset_postdata();
// }


// function add_council_to_meetings(){
//     $args = array(  
//         'post_type' => 'mm365_meetings',
//         'posts_per_page' => -1, 
//         'orderby' => 'date', 
//     );
//     $loop = new WP_Query( $args );  
//     while ( $loop->have_posts() ) : $loop->the_post(); 
//       update_post_meta(get_the_ID(), 'mm365_proposer_council_id','6928');
//       update_post_meta(get_the_ID(), 'mm365_attendees_council_id','6928');
//     endwhile;
//     wp_reset_postdata();
// }

// function add_council_to_certificates(){
//     $args = array(  
//         'post_type' => 'mm365_certification',
//         'posts_per_page' => -1, 
//         'orderby' => 'date', 
//     );
//     $loop = new WP_Query( $args );  
//     while ( $loop->have_posts() ) : $loop->the_post(); 
//       update_post_meta(get_the_ID(), 'mm365_submitted_council','6928');
//     endwhile;
//     wp_reset_postdata();
// }


/* Find all 'business_user' ID, updated 'primary_msdc' in 'mmsdc_uwp_usermeta' table */

// function add_council_to_business_users(){
//   $business_users = get_users( array( 'role__in' => array( 'business_user') ) );
//   foreach ( $business_users as $user ) {
//     change_userdc( $user->ID );
//   }
// }

// function change_userdc($user_id){
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'uwp_usermeta';
//     $wpdb->update( $table_name, array( 'primary_msdc' => '6928'), array( 'user_id' => $user_id) );

// }


/* Find all match requests and use serialize function on mm365_matched_companies and mm365_matched_companies_scores*/

// function fix_matchrequests_resultarrays(){

//    //get all match requests
//     $args = array(  
//         'post_type' => 'mm365_matchrequests',
//         'posts_per_page' => -1, 
//         'orderby' => 'date', 
//         //'post__not_in' => [ 6945, 6966, 6965 ],
//     );
//     $loop = new WP_Query( $args );  
//     while ( $loop->have_posts() ) : $loop->the_post(); 
      
//      $exiting_matched_companies  = json_decode(get_post_meta( get_the_ID(), 'mm365_matched_companies', true ));
//      update_post_meta( get_the_ID(), 'mm365_matched_companies', maybe_serialize($exiting_matched_companies));

//      $exiting_scores  = json_decode(get_post_meta( get_the_ID(), 'mm365_matched_companies_scores', true ));
//      update_post_meta( get_the_ID(), 'mm365_matched_companies_scores', maybe_serialize($exiting_scores));

//     endwhile;
//     wp_reset_postdata();

// }


// function datascrub_companies(){
//   //address , phone, alt phone, alt email, current customers
//   // mm365_alt_email
//   // mm365_alt_phone
//   // mm365_company_address
//   // mm365_main_customers
//   // mm365_website
//   // mm365_zip_code

//     $args = array(  
//         'post_type' => 'mm365_companies',
//         'posts_per_page' => -1, 
//         'orderby' => 'date', 
//         'fields'  => 'ids', // Reduce memory footprint
//     );
//     $loop = new WP_Query( $args );  
//     while ( $loop->have_posts() ) : $loop->the_post(); 
//        update_post_meta( get_the_ID(), 'mm365_alt_email', 'dummymmsdc_'.get_the_ID().'@yopmail.com');
//        update_post_meta( get_the_ID(), 'mm365_alt_phone', '000 0000000');
//        update_post_meta( get_the_ID(), 'mm365_company_phone', '000 0000000');      
//        update_post_meta( get_the_ID(), 'mm365_company_address', '');
//        update_post_meta( get_the_ID(), 'mm365_main_customers', '');
//        update_post_meta( get_the_ID(), 'mm365_website', 'example.com');
//        update_post_meta( get_the_ID(), 'mm365_zip_code', '000000');
//     endwhile;
//     wp_reset_postdata();

// }


//Add title as meta value to existing companies
// function mmdatafix_companynames_for_search(){

//     $args = array(  
//         'post_type' => 'mm365_companies',
//         'posts_per_page' => -1, 
//         'orderby' => 'date', 
//         'fields'  => 'ids', // Reduce memory footprint
//     );
//     $loop = new WP_Query( $args );  
//     while ( $loop->have_posts() ) : $loop->the_post(); 
//        $get_title = get_the_title( get_the_ID() );
//        update_post_meta( get_the_ID(), 'mm365_company_name', $get_title);
//     endwhile;
//     wp_reset_postdata();

// }

// function mmdatafix_companynames_in_certificate(){

//       $args = array(  
//           'post_type' => 'mm365_certification',
//           'posts_per_page' => -1, 
//           'orderby' => 'date', 
//           'fields'  => 'ids', // Reduce memory footprint
//       );
//       $loop = new WP_Query( $args );  
//       while ( $loop->have_posts() ) : $loop->the_post(); 
//          $submited_comp_id = get_post_meta(get_the_ID(), 'mm365_submitted_by', true );
//          $get_title = get_the_title( $submited_comp_id);
//          update_post_meta( get_the_ID(), 'mm365_submitted_companyname', $get_title);
//       endwhile;
//       wp_reset_postdata();
// }

// function mmdatafix_amp_in_name_fix(){

//     $my_query = new WP_Query( array(
//       'post_type' => 'mm365_companies',
//       'post_status' => 'publish',
//       'posts_per_page' => -1,
//       'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
//       'update_post_meta_cache' => false,
//       'update_post_term_cache' => false,
//       's' => "&amp;"
//     ));

//     while ( $my_query->have_posts() ) : $my_query->the_post(); 
//       $new_title = str_replace("&amp;", "&", get_the_title());
//       $post_update = array(
//         'ID'         => get_the_ID(),
//         'post_title' => $new_title
//       );
//       wp_update_post( $post_update );
//     endwhile;

//   }
//   
//   
//   

/**
 * ---------------------------------------
 * certification update as company meta
 * ---------------------------------------
 */

//  function mmdatafix_update_certification_data_to_company(){

//   //Loop through certificates
  
//   $args = array(
//     'post_type'      => 'mm365_certification',
//     'post_status'    => 'publish',
//     'fields'         => 'ids', // Reduce memory footprint
//     'posts_per_page' => -1, 
//     'order'          => 'DESC',
//     'order_by'       => 'modified',
//     'meta_query' => array(
//       array(
//           'key'     => 'mm365_certificate_status',
//           'value'   => 'verified',
//           'compare' => '=',
//       )
//     )
//   );

//     $my_query  = new WP_Query($args);
//     while ( $my_query->have_posts() ) : 
//         $my_query->the_post(); 
//         $verified_companies[] = get_post_meta( get_the_ID(), 'mm365_submitted_by', true );
//     endwhile;

//   //Get company ids of all approved certificates

//   foreach($verified_companies as $company_id)
//   {
//         //Add meta info to those companies - certified - yes / expired
//         if ( metadata_exists( 'post', $company_id, 'mm365_certification_status' ) ) {
//           add_post_meta( $company_id, 'mm365_certification_status', 'verified' );
//         }else{
//           update_post_meta( $company_id, 'mm365_certification_status', 'verified' );
//         }
    
//   }

//  }


/**
 * ---------------------------------------
 * company description update
 * ---------------------------------------
 */
/*function mm365datafixer_company_description_update(){

  
  $filename= plugin_dir_path( __FILE__ ). '\actualdata.csv';

  //Read CSV file
   $row = 1;
 
  if (($handle = fopen($filename, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, null, ",")) !== FALSE) {
          $num = count($data);
          //echo "<p> $num fields in line $row: <br /></p>\n";
          $row++;
          // for ($c=0; $c < $num; $c++) {
          //     echo $data[0] . "<br />\n";
          // }
          echo $data[2] .'-'. $data[1] . "<br /><br /><br />\n";
          //update_post_meta($data[2],'mm365_company_description',$data[1]);
      }
      fclose($handle);

  }
 
  //Find IDS

  //Rewrite - mm365_company_description 

  //Complete

}*/

/**
 * ---------------------------------------
 * Put duplicates to pending review
 * ---------------------------------------
 */

//  function mm365datafixer_remove_duplicate_companies(){

//   $company_ids = [13298,13241,4524,14337,13436,13300,4551,14249,14214,4968,13761,14692,13290,4378,11459,8268,13430,14284,14192,13935,13366,14285,14209,14104,14102,13288,4202,14401,13251,4606,8588,4586,10647,13295,13282,13294,14697,13418,13296,13812,14286,4180,14140,4443,13522,13421,13592,4471,10642,4306,14422,13311,4376];
  

//   foreach ($company_ids as $cmp_id) {

//     $args = array(
//       'ID'            => $cmp_id,
//       'post_status'   => 'pending',
//     );
//     wp_update_post( $args );
//   }


//  }


/**
 * NAICS DB TABLE
 * 
 */

 function mm365_2017_naics_codes_table()
 {

  //Create table
  global $wpdb;
	
	$table_name = $wpdb->prefix . '2017_naics_codes';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		code mediumint(11) NOT NULL,
		title varchar(150) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	dbDelta( $sql );

  //Import NAICS codes
  $f = fopen(WP_PLUGIN_DIR.'/fixdata/naics_db/2017_naics.csv', "r");
      while($row = fgetcsv($f)) {
        $wpdb->insert( 
          $table_name, 
          array( 
            'code' => $row[1], 
            'title' => $row[2]
          ) 
        );
      }
  fclose($f);
  wp_die();

}
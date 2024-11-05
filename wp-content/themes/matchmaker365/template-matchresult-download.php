<?php
/**
 * Template Name: Match Result - Download
 *
 */
$mr_id   = $_REQUEST['mr_id'];
$nonce = $_REQUEST['_wpnonce'];

if ( ! wp_verify_nonce( $nonce, 'download_results' ) ) {
    die( __( 'Security check', 'textdomain' ) ); 
} else {
  
$user = wp_get_current_user();
if(is_user_logged_in() AND in_array( 'business_user', (array) $user->roles )  OR in_array( 'mmsdc_manager', (array) $user->roles )){
  
  apply_filters('mm365_matchrequest_result_download',$mr_id); 

  get_header();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">


  <!-- Panel ends -->  
  </div>
</div>
<?php } else {       $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      wp_redirect(wp_login_url($actual_link));}?>
<?php
get_footer();
}
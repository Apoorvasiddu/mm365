<?php
/**
 * Template Name: Reports - Quick Reports
 *
 */

 do_action('mm365_helper_check_loginandrole',['mmsdc_manager','council_manager','business_user']);

$type   = $_REQUEST['type'];
$period = $_REQUEST['period'];
$meta   = $_REQUEST['meta'];

//if admin is filtering council
if(isset($_REQUEST['sacouncilfilter'])){
  $sacouncilfilter = $_REQUEST['sacouncilfilter'];
}else $sacouncilfilter = NULL;


$user = wp_get_current_user();

  switch($type){
    case 'company':
      apply_filters('mm365_admin_quickreports_companies', $period,$meta, $sacouncilfilter);
      break;

    case 'match':
      apply_filters('mm365_admin_quickreports_matchrequests', $period,$meta, $sacouncilfilter);
      break;

    case 'certificates':
        apply_filters('mm365_admin_quickreports_certification', $period,$meta, $sacouncilfilter);
        break;
    
    case 'meetings':
          apply_filters('mm365_meetings_quick_reports_download', $period,$meta, $sacouncilfilter);
          break;
  }
  


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
<?php
get_footer();
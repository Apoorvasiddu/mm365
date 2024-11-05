<?php
/**
 * Template Name: Admin - List All Match Requests
 *
 */
$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole',['mmsdc_manager','council_manager']);
get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">
  <!-- For super admin -->
  <?php 
  if ( in_array( 'mmsdc_manager', (array) $user->roles ) ) {
     get_template_part( 'template-parts/admin','matchrequests' ); 
  }
  ?>
  
  <!-- For Council manager -->
  <?php 
  if ( in_array( 'council_manager', (array) $user->roles ) ) {
     get_template_part( 'dc-manager/list','matchrequests' ); 
  }
  ?>

  </div>
</div>

 <?php  get_footer();
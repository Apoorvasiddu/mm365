<?php
/**
 * Template Name: View Reports Filtered - Consolidated
 *
 */
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

get_header();
?>



<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">

   

<section class="row admin-dash-filter">
    <div class="col-12"><h3 class="page_main_heading">View - Consolidated report between <span id="conrep_append_period_text"></span></h3></div>
</section>

<section class="row">
  <div class="col-12 x-scroll">
    <table id="viewreports_filtered_consolidated" data-intro="Count of registartions and match requests for the selected period"  class="matchrequests-list table table-striped" cellspacing="0" width="100%">
        <thead class="thead-dark">
                        <th><h6>Council</h6></th>                    
                        <th><h6>Suppliers registered</h6></th>
                        <th><h6>Buyers Registered</h6></th>
                        <th><h6>Pending Match requests</h6></th>
                        <th><h6>Approved Match requests</h6></th>
                        <th><h6>Auto Approved Match requests</h6></th>
                        <th><h6>Completed Match requests</h6></th>
                        <th><h6>Cancelled Match requests</h6></th>
                        <th><h6>Meetings Scheduled</h6></th>
        </thead>
      <tbody></tbody>
    </table>
  </div>
</section>


  </div>
</div>

<?php
get_footer();
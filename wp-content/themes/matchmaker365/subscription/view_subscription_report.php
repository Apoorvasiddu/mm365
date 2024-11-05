<?php
/**
 * Template Name: View Subscription Report
 *
 */
if (!wp_verify_nonce( $_REQUEST['_wpnonce'], 'subscription_report' )) {
    die();
}

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
        <div class="col-12"><h3 class="page_main_heading">Subscription Report</h3></div>
    </section>

    <section class="row">
    <div class="col-12 x-scroll">
        <table id="view_reports_subscription" data-intro=""  class="matchrequests-list table table-striped" cellspacing="0" width="100%">
            <thead class="thead-dark">
                            <th class="no-sort"><h6>Company Name</h6></th>   
                            <th class="no-sort"><h6>Council</h6></th>                  
                            <th class="no-sort"><h6>Type</h6></th>
                            <th class="no-sort"><h6>Subscription Type</h6></th>
                            <th class="no-sort"><h6>Start date</h6></th>
                            <th class="no-sort"><h6>End date</h6></th>
                            <th class="no-sort"><h6>Status</h6></th>
            </thead>
        <tbody></tbody>
        </table>
    </div>
    </section>


  </div>
</div>

<?php
get_footer();
<?php
/**
 * Template Name: Reports - Consolidated
 *
 */


$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

if (!empty($_POST)):
    $from = $_POST['from_date'];
    $to = $_POST['to_date'];
    if(isset($from) AND isset($to)){
        //$consolidated_report->download_consolidated_report();
        apply_filters('mm365_download_consolidated_report',1);
    }
endif;
get_header();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">

  <h2 class="heading-large pbo-20">Consolidated Report</h2>


<!-- Report Generation Form -->
 <form method="post" id="mm365_generate_report_consolidated" action="#"  data-parsley-validate enctype="multipart/form-data" >
 <!-- <form method="post" id="mm365_generate_report_comapny" action="#"  data-parsley-validate enctype="multipart/form-data" > -->
  <section class="company_preview">

      <div class="form-row form-group" data-intro="Count of registrations and match requests created on the platform for a selected period">
         <div class="col-lg-4">
                <label for="from_date">From date<span>*</span></label>
                <input class="form-control from_date" type="text" required name="from_date" data-parsley-errors-container=".frmdateError"> 
                <span class="calendar-icon"></span>   
                <div class="frmdateError"></div>     
         </div>
         <div class="col-12 d-block d-sm-none pbo-30"></div>
         <div class="col-lg-4">
                <label for="to_date">To date<span>*</span></label>
                <input id="secondRangeInput" class="form-control to_date" type="text" required name="to_date" data-parsley-errors-container=".todateError">  
                <span class="calendar-icon"></span>
                <div class="todateError"></div>     
         </div>
        
      </div>
      <div class="form-row form-group">
          <div class="col-lg-12"><label><small>*Maximum duration between From date and To date is limited to 1 year</small></label></div>
      </div>
      
      <!-- F2 -->

     
  </section>

<div class="form-row mto-30">
  <div class="col-lg-12 text-right">
        <?php wp_nonce_field('mm365-consolidated-report','_consolidatedreportnonce'); ?>
        <input type="hidden" name="action" value="mm365-generate-report-consolidated">
        <button data-intro="Download the detailed report in XLS format"  name="download-sbt" id="download-report-consolidated"  class="btn btn-primary"><?php esc_html_e('Download Report', 'mm365') ?></button>
        <button data-intro="View the report with minimum details" name="view-sbt" id="company-view-report-consolidated" data-redirect="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'avr_consolidated' ), site_url().'/admin-view-report-consolidated' ) ?>"  class="btn btn-primary"><?php _e('View Report', 'mm365') ?></button>
  </div>
</div>


    </form>



  <!-- Panel ends -->  
  </div>
</div>

<?php
get_footer();
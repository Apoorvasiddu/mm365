<?php
/**
 * Template Name: Reports - Certification
 *
 */


$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

if (!empty($_POST)):
  //$certificationClass->generate_report();
  apply_filters('mm365_admin_filteredreports_certification',1);
endif;

if(isset($_COOKIE['report_generate_status_certification'])){
  $status = $_COOKIE['report_generate_status_certification'];
}else $status = NULL;

if (isset($_COOKIE['report_generate_status_certification'])) {
  unset($_COOKIE['report_generate_status_certification']); 
  setcookie('report_generate_status_certification', null, -1, '/'); 
}

get_header();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">

  <h2 class="heading-large pbo-20">Certification Report</h2>
<?php if($status != NULL AND $status =='err'):?>
  <div id="ajax-warnings"><div class="alert alert-danger" role="alert"><h4 class="alert-heading">Sorry no records found!</h4><p>Please try with new set of parameters</p></div></div>
<?php endif; ?>
<!-- Report Generation Form -->
 <form method="post" id="mm365_generate_certification_report" action=""  data-parsley-validate enctype="multipart/form-data" >
 <!-- <form method="post" id="mm365_generate_report_comapny" action="#"  data-parsley-validate enctype="multipart/form-data" > -->
  <section class="company_preview" data-intro="Generate (View or Download) list of companies and use the parameters to filter out the results">

      <div class="form-row form-group">
      <!-- <span>*</span> -->
         <div class="col-lg-3"  data-intro="Select the date range.">
                <label for="from_date">Uploaded From</label>
                <input class="form-control from_date_ur" type="text"  name="from_date" data-parsley-errors-container=".frmdateError"> 
                <span class="calendar-icon"></span>   
                <div class="frmdateError"></div>     
         </div>
         <div class="col-12 d-block d-sm-none pbo-30"></div>
         <div class="col-lg-3"  data-intro="The date values are checked based on the company registration date. The maximum duration between From date and To date is one year">
                <label for="to_date">To</label>
                <input id="secondRangeInputUR" class="form-control to_date_ur" type="text"  name="to_date" data-parsley-errors-container=".todateError">  
                <span class="calendar-icon"></span>
                <div class="todateError"></div>     
         </div>
         <div class="col-lg-3" data-intro="Filter out companies associated with a specific council">
               <label id="councilFilter_label" for="councilFilter">Council</label>
               <select id="councilFilter" name="council_filter" class="form-control">
                  <option value="">All Councils</option>
                  <?php
                    apply_filters('mm365_dropdown_councils', array());
                  ?>
                </select>
         </div>
         <div class="col-lg-3" data-intro="Certificate Status">
               <label id="certificateStatus_label" for="certificateStatus">Status</label>
               <select id="certificateStatus" name="certificate_status" class="form-control">
                  <option value="">All</option>
                  <option value="verified">Verified</option>
                  <option value="pending">Pending</option>
                  <option value="expired">Expired</option>
                  <option value="unapproved">Unapproved</option>
                </select>
         </div>
      </div>
      <!-- <div class="form-row form-group">
          <div class="col-lg-12"><label><small>*Maximum duration between From date and To date is limited to 1 year</small></label></div>
      </div> -->
      
      <!-- F2 -->


  </section>

<div class="form-row mto-30">
  <div class="col-lg-12 text-right">
        <?php //wp_nonce_field('mm365-report-company','_companyreportnonce'); ?>
        <input type="hidden" name="action" value="mm365-generate-report-companyreg">
        <button data-intro="Download report with all data in XLS format." name="download-sbt" id="download-report"  class="btn btn-primary"><?php esc_html_e('Download Report', 'mm365') ?></button>

  </div>
</div>


    </form>



  <!-- Panel ends -->  
  </div>
</div>

<?php
get_footer();
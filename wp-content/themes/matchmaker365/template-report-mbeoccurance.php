<?php
/**
 * Template Name: Reports - MBE Occurrence
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

    <div class="row">
      <div class="col-8"><h2 class="heading-large pbo-20">MBEs Appeared in Match Results</h2></div>
      <?php $url = site_url('download-mbe-occurrence-report'); ?>
      <div class="col-4 text-right"><a href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'download_occurrence_report' ), $url ); ?>" class="btn btn-primary dash-report-btn">Download Report</a></div>
    </div>
        
      <section class="company_preview">

          <table id="mbeoccurance_list_councils" class="mm365datatable-list table table-striped"  cellspacing="0" width="100%" data-intro="List of companies and the count of their appearances in match requests ">
            <thead class="thead-dark">
              <tr>
                <th width="20%"  class="no-sort"><h6>MBE Name</h6></th>
                <th class="no-sort"><h6>Number of appearances</h6></th>
                <th class="no-sort"><h6>Contact Person</h6></th>
                <th class="no-sort"><h6>Email</h6></th>
                <th class="no-sort"><h6>Phone</h6></th>
              </tr>
            </thead>
            <tbody>
            <!-- Content will be ajax loaded here -->
            </tbody>
          </table>
          
      </section>
  <!-- Panel ends -->  
  </div>

</div>

<?php
get_footer();
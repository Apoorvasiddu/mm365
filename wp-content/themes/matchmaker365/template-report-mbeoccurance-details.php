<?php
/**
 * Template Name: Reports - MBE Occurrence Details
 *
 */


$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

//Company ID
$cid = $_REQUEST['cid'];

get_header();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
  </div>

  <div class="dashboard-content-panel">
      <h2 class="heading-large pbo-20">Match requests which <a target='_blank' href='<?php echo site_url('view-company') ?>?cid=<?php echo $cid ?>'><?php echo get_the_title($cid) ?></a> matched for</h2>
      <section class="company_preview">

          <table id="mbeoccurance_details" data-company_id='<?php echo esc_html($cid); ?>' class="mm365datatable-list table table-striped"  cellspacing="0" width="100%" data-intro="List of companies and the count of their appearances in match requests ">
            <thead class="thead-dark">
              <tr>
                <th width="30%"  class="no-sort"><h6>Details of services or products looking for</h6></th>
                <th class="no-sort"><h6>Requested by</h6></th>
                <th class="no-sort"><h6>Council</h6></th>
                <th class="no-sort"><h6>Date Requested</h6></th>
                <th class="no-sort"><h6>Location where the products or services are required</h6></th>
                <th class="no-sort"><h6></h6></th>
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
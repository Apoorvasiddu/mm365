<?php
/**
 * Template Name: View Reports Filtered - Match Requests
 *
 */
$user = wp_get_current_user();

$council_id = apply_filters('mm365_helper_get_usercouncil',$user->ID);

do_action('mm365_helper_check_loginandrole',['mmsdc_manager','council_manager','super_buyer']);

get_header();
?>

<style>
  select.form-control,
  #councilFilter {
    display: inline;
    width: 200px;
    margin-left: 5px;
  }

  #viewreports_filtered_matchrequests_admin_filter label:last-child {
    width: 50%;
  }

  #viewreports_filtered_matchrequests_admin_filter {
    display: flex;
    align-items: start;
    justify-content: flex-end;
  }

  #viewreports_filtered_matchrequests_admin_filter label input {
    width: 80%
  }
</style>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>

  </div>
  <div class="dashboard-content-panel">


    <section class="row admin-dash-filter">
      <div class="col-12">
        <h3 class="page_main_heading">View - Match requests <span id="append_kind_text_mr"></span> from <span
            id="append_period_text_mr"></span></h3>
      </div>
    </section>

    <?php if ($council_id == ''): ?>
      <!-- Council filter -->
      <div class="council-filter">
        <label id="councilFilter_label" for="councilFilter">Council:
          <select id="councilFilter" class="form-control">
            <option value="">All Councils</option>
            <?php
              apply_filters('mm365_dropdown_councils', array());
              ?>
          </select>
        </label>
      </div>
    <?php endif; ?>

    <table id="viewreports_filtered_matchrequests_admin" class="matchrequests-list table table-striped" cellspacing="0"
      width="100%">
      <thead class="thead-dark">
        <tr>
          <th>
            <h6>Requester Company</h6>
          </th>
          <?php if ($council_id == ''): ?>
            <th class="no-sort">
              <h6>Council</h6>
            </th>
          <?php endif; ?>
          <th class="no-sort">
            <h6>Services or Products</h6>
          </th>
          <th class="no-sort">
            <h6>Industry</h6>
          </th>
          <th width="20%" class="no-sort">
            <h6>Request details</h6>
          </th>
          <th>
            <h6>Match status</h6>
          </th>
          <th>
            <h6>Reason for closure</h6>
          </th>
          <th class="no-sort">
            <h6>Message</h6>
          </th>
          <th width="30%" class="no-sort">
            <h6>Matched companies</h6>
          </th>
          <th class="no-sort">
            <h6>Approved by</h6>
          </th>
          <th>
            <h6>Date</h6>
          </th>
        </tr>
      </thead>
      <tbody>

      </tbody>
    </table>



  </div>
</div>

<?php
get_footer();
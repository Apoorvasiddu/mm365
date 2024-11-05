<?php
/**
 * Template Name: User - Meetings Scheduled
 *
 */
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['business_user']);

//Check if user has active registration else redirect
do_action('mm365_helper_check_companyregistration', 'register-your-company');

get_header();
?>
<style>
  select.form-control,
  #councilFilter {
    display: inline;
    width: 200px;
    margin-left: 5px;
  }

  #meeting_scheduled_list_filter label:last-child {
    width: 50%;
  }

  #meeting_scheduled_list_filter {
    display: flex;
    align-items: start;
  }

  #meeting_scheduled_list_filter label input {
    width: 80%
  }
</style>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>

  </div>
  <div class="dashboard-content-panel">


    <h3 class='heading-large'>My Meetings</h3>
    <!-- List Existing Match Requests -->
    <div class="matchrequests-admin-view"
      data-intro="List of meetings which you have initiated to companies which matched against your match requests"
      data-step="1">

      <!-- Council filter -->
      <div class="council-filter">
        <label id="councilFilter_label" for="councilFilter">Council:
          <select id="councilFilter" class="form-control">
            <option value="">All Councils</option>
            <?php
            apply_filters('mm365_dropdown_councils', NULL)
            ?>
          </select>
        </label>
      </div>

      <table id="meeting_scheduled_list" data-timezone="0" data-offset="0" data-dst="0"
        class="matchrequests-list table table-striped" cellspacing="0" width="100%">
        <thead class="thead-dark">
          <tr>
            <!-- <th>#</th> -->
            <th>
              <h6>Company</h6>
            </th>
            <th class="no-sort">
              <h6>Council</h6>
            </th>
            <th class="no-sort">
              <h6>Contact person</h6>
            </th>
            <th class="no-sort" width="20%">
              <h6>Meeting title</h6>
            </th>
            <th class="no-sort">
              <h6>Proposed time slots</h6><small class="show_user_tz"></small>
            </th>
            <th class="no-sort">
              <h6>Accepted time slot</h6><small class="show_user_tz"></small>
            </th>
            <th>
              <h6>Current status</h6>
            </th>
            <th class="no-sort"></th>
            <th class="no-sort"></th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>




  </div>
</div>

<?php get_footer();
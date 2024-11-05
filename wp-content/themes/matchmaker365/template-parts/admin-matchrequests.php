<?php
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);
?>
<style>
  select.form-control,
  #councilFilter {
    display: inline;
    width: 200px;
    margin-left: 5px;
  }

  #matchlist_admin_filter label:last-child {
    width: 50%;
  }

  #matchlist_admin_filter {
    display: flex;
    align-items: start;
  }

  #matchlist_admin_filter label input {
    width: 80%
  }
</style>
<h3 class='heading-large'><?php esc_html_e('Approve Match Requests', 'mm365'); ?></h3>
<!-- List Existing Match Requests -->
<div class="matchrequests-admin-view">

  <!-- Council filter -->
  <div class="council-filter">
    <label id="councilFilter_label" for="councilFilter">Council:
      <select id="councilFilter" class="form-control"
        data-intro="Filter the match requests submitted by the companies from selected council" data-step="2">
        <option value="">
          <?php esc_html_e('All Councils', 'mm365'); ?>
        </option>
        <?php
        apply_filters('mm365_dropdown_councils', NULL);
        ?>
      </select>
    </label>
  </div>


  <table id="matchlist_admin" class="matchrequests-list table table-striped" cellspacing="0" width="100%"
    data-position="top"
    data-intro="Match requests submitted by the users. The columns include company name, the council which they belongs to, approval privilege of the council, requested date and time, location where the services/products are needed, keywords they searched for along with status and action links">
    <thead class="thead-dark">
      <tr>
        <!-- <th>#</th> -->
        <th width="15%">
          <h6>Company name</h6>
        </th>
        <th class="no-sort">
          <h6>Type</h6>
        </th>
        <th width="8%" class="no-sort">
          <h6>Council</h6>
        </th>
        <th width="5%" class="no-sort">
          <h6>NAICS Codes</h6>
        </th>

        <th>
          <h6>Location where products or services are required</h6>
        </th>
        <th width="20%">
          <h6>Details of services or products looking for </h6>
        </th>
        <th>
          <h6>Status</h6>
        </th>
        <th>
          <h6>Requested date & time</h6>
        </th>
        <th width="10%" class="no-sort"></th>

      </tr>
    </thead>
    <tbody>

    </tbody>
  </table>
</div>
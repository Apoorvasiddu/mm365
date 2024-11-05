<?php
/**
 * Template Name: Admin - Match Request Manage
 *
 */
$mr_id = $_REQUEST['mr_id'];
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['business_user', 'council_manager', 'mmsdc_manager']);

if (in_array('council_manager', (array) $user->roles)) {

  $council_id = apply_filters('mm365_helper_get_usercouncil', $user->ID);
  $can_access = apply_filters('mm365_council_content_access_check', $mr_id, $council_id, 'mm365_requester_company_council');

} else {
  $can_access = TRUE;
}

//If its a pending post (redirect back to )
if (get_post_status($mr_id) == 'pending') {
  wp_redirect(site_url('admin-matchrequests-listing'));
}

get_header();

//Match status label change

$match_status = get_post_meta($mr_id, 'mm365_matchrequest_status', true);
switch ($match_status) {
  case 'nomatch':
    $mr_stat_display = "<span class='" . $match_status . "'>No Match</span>";
    break;
  case 'auto-approved':
    $mr_stat_display = "<span class='" . $match_status . "'>Auto Approved</span>";
    break;
  default:
    $mr_stat_display = "<span class='" . $match_status . "'>" . ucfirst($match_status) . "</span>";
    break;
}

?>

<style>
  select.form-control,
  #councilFilter {
    display: inline;
    width: 200px;
    margin-left: 5px;
  }

  #mr-admin-list_filter label:last-child {
    width: 50%;
  }

  #mr-admin-list_filter {
    display: flex;
    align-items: baseline;
  }

  #mr-admin-list_filter label input {
    width: 80%
  }

  .select2-container {
    z-index: 999999;
  }
</style>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>
  <section class="dashboard-content-panel">
    <?php if ($can_access == TRUE): ?>

      <!-- Panel starts -->
      <a href="#" onclick="location.replace(document.referrer)" class="">
        <h3 class='heading-large'>
          <img class="back-arrow" src="<?php echo get_template_directory_uri() ?>/assets/images/arrow-left.svg"
            height="36px" alt="">&nbsp;View Match
        </h3>
      </a>

      <?php
      //If match status is completed look for company grades
      $grades_given = array();
      if ($match_status == 'completed') {
        $grades_list = get_post_meta($mr_id, 'mm365_match_grade');
        foreach ($grades_list as $key => $value) {
          $split_grade = explode("|", $value);
          $grades_given[$split_grade[0]] = $split_grade[1];
        }
      }
      ?>
      <!-- Match Request minor details -->

      <!-- Match Results -->
      <?php
      if (in_array('mmsdc_manager', (array) $user->roles) or in_array('council_manager', (array) $user->roles)) {
        $company_id = get_post_meta($mr_id, 'mm365_requester_company_id', true);
        ?>
        <div class="row">
          <div class="col-md-4">
            <h4 class="heading-medium text-center text-md-left">Match Request Details</h4>
          </div>


          <div
            class="col-md-8 d-flex flex-wrap flex-md-nowrap align-items-center justify-content-around justify-content-md-end gap-15">

            <?php
            //Coucnil manager with approval permission can use the Force no match and add companies feature
            if (array_intersect(array('mmsdc_manager', 'council_manager_approvers'), (array) $user->roles)) {
              $mr_status = get_post_meta($mr_id, 'mm365_matchrequest_status', true);

              if (in_array($mr_status, array("pending", "auto-approved", "approved"))): ?>
                <a href="#"
                  data-intro="Manually add more companies to the result set. NB: This is an advaced option and is irreversible. Please do not proceed adding companies without verifying their details"
                  data-fancybox="search-companies-mr" data-src="#search-companies-mr" data-mrid="<?php esc_html_e($mr_id); ?>"
                  class="btn btn-primary green">Add more results</a>
                <?php
              endif;

              if (in_array($mr_status, array("pending", "auto-approved"))): ?>
                <a href="#"
                  data-intro="Forcefully remove the result set and put the match request to 'No Match' status if the match request details is ambiguous. This action is irreversible, please proceed with caution"
                  data-mrid="<?php esc_html_e($mr_id); ?>"
                  data-redirect_url="<?php esc_html_e(site_url('admin-matchrequests-listing')); ?>" id="sa-force-nomatch"
                  class="btn btn-primary red">Force to 'No Match'</a>
              <?php endif;
            }
            ?>

            <?php if (in_array('mmsdc_manager', (array) $user->roles)) { ?>
              <a href="#"
                data-intro="Option to delete match request at the discretion of the Manager. This action is irreversible, please proceed with caution"
                data-mrid="<?php esc_html_e($mr_id); ?>"
                data-redirect_url="<?php esc_html_e(site_url('admin-matchrequests-listing')); ?>" id="sa-delete-mr"
                class="btn btn-primary red">Delete this Match Request</a>
            <?php } ?>

            <?php

            //$mm365_helper->matchstatus_display($mr_id); 
            apply_filters('mm365_matchrequest_show_status', $mr_id);
            ?>

          </div>


        </div>

        <!-- Search Company to add them to MR -->

        <form id="search-companies-mr" action="" method="post" data-parsley-validate
          style="display: none; width: 100%; max-width: 660px;" class=""
          data-redirect_url="<?php esc_html_e(site_url('admin-match-request-manage?mr_id=' . $mr_id)); ?>">
          <h4>Search Companies</h4>
          <small>Select companies to add them to match request. Duplicate records will be ignored while adding.</small>
          <div class="form-row mto-30">
            <select class="find-companies-to-add" name="companies_to_add[]" multiple required style="width:100%"
              data-parsley-errors-container=".findcompaniesError"></select>
            <div class="findcompaniesError"></div>
          </div>
          <div class="form-row mto-30">
            <input type="hidden" name="adding_to_mr_id" value="<?php esc_html_e($mr_id); ?>">
            <button type="submit" name="add-companies-to-mr" id="add-companies-to-mr" data-redirect=""
              class="btn btn-primary">Add Companies</button>
          </div>
        </form>

        <!-- Search Company -->

        <section class="matchrequest-short-details-- mto-30"
          data-intro="Details of the match request submitted by the user. Optionally click 'More Details' link to show all the parameters of the match request">
          <?php apply_filters('mm365_matchrequest_admin_preview', $mr_id, 'publish'); ?>
        </section>

        <?php
        $matched_companies = maybe_unserialize(get_post_meta($mr_id, 'mm365_matched_companies', true));

        // foreach ($matched_companies as $value) {
        //   echo $value[0].'-';
        // }
     
        //Matched companies
        //print_r($matched_companies);echo "<br/>";

        //Companies in a confernce - get all suppliers
        //$suppliers_in_conf = apply_filters('mm365_offline_get_suppliers_in_conference',9178);
        //print_r($suppliers_in_conf);


        //Filter companies attending a conf

        if (!empty($matched_companies)):
          ?>
          </pre>
          <div class="row pto-30">
            <div class="col-12">
              <h4 class="heading-medium text-center text-md-left">Match Results</h4>
            </div>
          </div>


          <form action="manage_matched_companies" class="pbo-50" id="manage_matched_companies" method="post">
            <input type="hidden" name="mr_id" value="<?php echo $mr_id; ?>">
            <div class="table-responsive">

              <?php
              //For preselecting council
              $manager_council = apply_filters('mm365_helper_get_usercouncil', $user->ID);

              //Check if council has preselect enabled
              $cm_preselect = get_post_meta($manager_council, 'mm365_council_preselect', TRUE);

              //Get short name
              $shrtname = apply_filters('mm365_council_get_info', $manager_council);

              //upcoming conferences
              
              $upcomingConferences = apply_filters('mm365_offline_conferences_list', FALSE, TRUE);

              ?>

  
              <!--  filter -->
              <div class="council-filter">

                <label id="conferenceFilter_label" for="conferenceFilter">Conference:
                  <select id="conferenceFilter" class="form-control" data-intro="Filter companies attended in selected conference">
                    <option value="">-select-</option>

                    <?php foreach ($upcomingConferences as $conference) {
                            ?>
                            <option value="<?php echo esc_html($conference['ID']); ?>"><?php echo esc_html($conference['name']); ?></option>
                            <?php
                          }
                          ?>
              
                        </select>
                      </label>

                      <label id="councilFilter_label" for="councilFilter">Council:
                        <select id="councilFilter" class="form-control" data-intro="Filter companies from selected council">
                          <option value="">All Councils</option>
                    <?php
                    apply_filters('mm365_dropdown_councils', NULL, 'shortname');
                    ?>
                  </select>
                </label>
              </div>

              <table id="mr-admin-list" class="matchrequests-list table table-striped"
                data-intro="List of companies matched against the request .The result set is paged and by default each page will show 10 companies. The columns include check box to select the company to approve,  company name, company's council, serviceable location, company description (trimmed), approval status and meeting status. Up on clicking the check box the approve button will float on the bottom of the screen along with the count of companies selected for approval. Once all the valid results are selected , click ‘Approve’ ">
                <thead class="thead-dark">
                  <tr>
                    <th class="no-sort">
                      <?php if (!in_array($match_status, array('completed', 'cancelled', 'auto-approved')) and array_intersect(array('mmsdc_manager', 'council_manager_approvers'), (array) $user->roles)): ?>
                        <div class="md-checkbox md-checkbox-inline checkall">
                          <input id="checkAll" type="checkbox" name="checkAll">
                          <label for="checkAll"></label>
                        </div>
                      <?php endif; ?>
                    </th>
                    <th>
                      <h6>Company name</h6>
                    </th>
                    <th>
                      <h6>Council</h6>
                    </th>
                    <th>
                      <h6>Location where products or services are available</h6>
                    </th>
                    <th width="35%" class="no-sort">
                      <h6>Company description</h6>
                    </th>
                    <th>
                      <h6>Match status</h6>
                    </th>
                    <th>
                      <h6>Meeting status</h6>
                    </th>
                    <th>
                      <h6>Conf</h6>
                    </th>
                    <!-- <th><h6></h6></th> -->
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($matched_companies as $key => $value) {

                    ?>
                    <tr>
                      <td class="text-center">
                        <?php if (!in_array($match_status, array('completed', 'cancelled', 'auto-approved')) and array_intersect(array('mmsdc_manager', 'council_manager_approvers'), (array) $user->roles)): ?>
                          <div class="md-checkbox md-checkbox-inline">
                            <input id="cb-<?php echo $value[0]; ?>-<?php echo $mr_id; ?>" type="checkbox"
                              class="approve-cb-inline" name="matched_comp_id[]" <?php if ($value[1] == 1): ?>checked disabled<?php endif; ?> value="<?php echo $value[0]; ?>">
                            <label for="cb-<?php echo $value[0]; ?>-<?php echo $mr_id; ?>"></label>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td>

                        <strong>
                          <?php echo "<a target='_blank' href='" . site_url() . "/view-company?cid=" . $value[0] . "&mr_id=" . $mr_id . "'>";
                          ?>
                          <?php
                          echo get_the_title($value[0]);
                          ?>
                          </a>
                        </strong>
                        &nbsp;&nbsp;
                        <?php
                        if (array_key_exists($value[0], $grades_given)) {
                          switch ($grades_given[$value[0]]) {
                            case '1':
                              echo '<img src="' . get_template_directory_uri() . '/assets/images/green_grade.png">';
                              break;
                            case '2':
                              echo '<img src="' . get_template_directory_uri() . '/assets/images/yellow_grade.png">';
                              break;
                            default:
                              echo '<img src="' . get_template_directory_uri() . '/assets/images/red_grade.png">';
                              break;
                          }
                        }
                        ?>

                      </td>
                      <td>
                        <?php
                        $cmp_counil = get_post_meta($value[0], 'mm365_company_council', true);
                        echo apply_filters('mm365_council_get_info', $cmp_counil);
                        ?>
                      </td>
                      <td>
                        <?php
                        $countries = get_post_meta($value[0], 'mm365_cmp_serviceable_countries');
                        $states = get_post_meta($value[0], 'mm365_cmp_serviceable_states');
                        echo apply_filters('mm365_helper_multi_countries_state_display', $countries, $states);
                        ?>
                      </td>
                      <td>
                        <?php $company_info = get_post_meta($value[0], 'mm365_company_description', true);
                        echo strlen($company_info) > 250 ? substr(wp_strip_all_tags($company_info), 0, 250) . "..." : $company_info; ?>
                      </td>
                      <?php

                      if ($value[1] == 0) {
                        $status = 'pending';
                        $meeting_stat = '-';
                      } else {
                        $status = 'approved';
                        $current_meeting_status = apply_filters('mm365_meeting_status', $value[0], $mr_id, true, false);
                        if (isset($current_meeting_status['status']) and $current_meeting_status['status'] != '') {

                          $meeting_stat = ($current_meeting_status['status'] != '') ? "<span class='meeting_status " . $current_meeting_status['status'] . "'>" . preg_replace('/\_+/', ' ', ($current_meeting_status['status'])) . "</span>" : "-";

                        } else {
                          $meeting_stat = '';
                        }

                      } ?>
                      <td class=" <?php echo esc_attr($status); ?>">
                        <?php echo esc_html(ucfirst($status)); ?>
                      </td>
                      <td>
                        <?php echo $meeting_stat ?: '-'; ?>
                      </td>
                      <td data-cmpid='<?php echo $value[0]?>'>
                        <?php //echo $value[0]?>
                      </td>
                    </tr>

                  <?php } ?>
                </tbody>
              </table>
            </div>
            <section id="mm365-approve-matches">
              <div class="container">
                <div class="row">
                  <div class="col-6"> <span id="mm365-selected-matches"></span> <span
                      id="mm365-selected-matches-message">companies</span> selected</div>
                  <div class="col-6 text-right"><button type="submit"
                      class="btn btn-primary approve-matched-companies">Approve</button></div>
                </div>
              </div>
            </section>

          </form>
        <?php else: ?>
          <div class="row">
            <div class="col-12">
              <h4 class="heading-medium text-center">No valid matches found!</h4>
            </div>
          </div>
        <?php endif; ?>
      <?php } ?>

  </div>
<?php else: ?>
  <h2>Restricted</h2>
<?php endif; ?>
<!-- Panel ends -->
</section>

</div>

<?php
get_footer();
<?php
/**
 * Template Name: Reports - Match Requests
 *
 */
$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

if (!empty($_POST)) {
  $from = $_POST['from_date'];
  $to = $_POST['to_date'];
  if (isset($from) and isset($to)) {
    /*@PARAM Slug of the page*/
    apply_filters('mm365_admin_filteredreports_matchrequests','reports-match-requests'); 
  }
}

if (isset($_COOKIE['report_generate_status'])) {
  $status = $_COOKIE['report_generate_status'];
} else
  $status = NULL;

if (isset($_COOKIE['report_generate_status'])) {
  unset($_COOKIE['report_generate_status']);
  setcookie('report_generate_status', null, -1, '/');
}


get_header();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>

  </div>
  <div class="dashboard-content-panel">

    <h2 class="heading-large pbo-20">Match Requests Report</h2>
    <?php if ($status != NULL and $status == 'err'): ?>
      <div id="ajax-warnings">
        <div class="alert alert-danger" role="alert">
          <h4 class="alert-heading">Sorry no records found!</h4>
          <p>Please try with new set of parameters</p>
        </div>
      </div>
    <?php endif;



    ?>

    <!-- Report Generation Form -->
    <form method="post" id="mm365_generate_report_matchrequests" action="" data-parsley-validate
      enctype="multipart/form-data">
      <section class="company_preview"
        data-intro="Generate (View or Download) list of match requests submitted by the users. Use the parameters to filter out the results based on various criteria">

        <div class="form-row form-group">
          <div class="col-lg-4" data-intro="Select the date range.">
            <label for="">From date<span>*</span></label>
            <input class="form-control from_date" type="text" required name="from_date"
              data-parsley-errors-container=".frmdateError">
            <span class="calendar-icon"></span>
            <div class="frmdateError"></div>
          </div>
          <div class="col-12 d-block d-sm-none pbo-30"></div>
          <div class="col-lg-4" data-intro="Maximum duration between From date and To date is one year">
            <label for="">To date<span>*</span></label>
            <input id="secondRangeInput" class="form-control to_date" type="text" required name="to_date"
              data-parsley-errors-container=".todateError">
            <span class="calendar-icon"></span>
            <div class="todateError"></div>
          </div>
          <div class="col-12 d-block d-sm-none pbo-30"></div>
          <div class="col-lg-4" data-intro="Status of the match requests at the time of report generation">
            <label for="">Match status</label>
            <select required data-parsley-errors-container=".match_statusError" name="match_status" id="match_status"
              class="form-control mm365-single">
              <!-- <option value="all">-Select-</option> -->
              <option value="all">Any Status</option>
              <option value="auto-approved">Auto Approved</option>
              <option value="approved">Approved</option>
              <option value="pending">Pending</option>
              <option value="nomatch">No Match</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <div class="match_statusError"></div>

            <!-- Child filter for closed status -->
            <div id="closurefilter-block">
              <br />
              <label for="" id="reason-label"></label> <br />

              <!-- Completed matches filter -->
              <select name="match_closure_filter" id="match_closure_filter_completed" class="form-control mm365-single">
                <option value="">-Select-</option>
                <?php
                $closure_reasons = apply_filters('mm365_helper_get_themeoption', 'closure_reasons');
                //sort($closure_reasons);
                foreach ($closure_reasons as $key => $value) {
                  if ($value['closure_completed_display_mode'] == 1) {
                    echo '<option>' . $value['reason_text'] . '</option>';
                  }
                }
                ?>
              </select>

              <!-- Cancelled matches filter -->
              <select name="match_closure_filter" id="match_closure_filter_cancelled" class="form-control mm365-single">
                <option value="">-Select-</option>
                <?php
                $closure_reasons = apply_filters('mm365_helper_get_themeoption', 'closure_reasons_cancelled');
                //sort($closure_reasons);
                foreach ($closure_reasons as $key => $value) {
                  if ($value['closure_ccancelled_display_mode'] == 1) {
                    echo '<option>' . $value['reason_text'] . '</option>';
                  }
                }
                ?>
              </select>
            </div>


          </div>
        </div>
        <div class="form-row form-group">
          <div class="col"><label><small>*Maximum duration between From date and To date is limited to 1
                year</small></label></div>
        </div>

        <!-- F2 -->

        <div class="form-row form-group">
          <div class="col-lg-4" data-intro="Company services or products which the buyer was looking for">
            <label for="">Company services or products</label>
            <select name="services[]" id="services" multiple class="form-control mm365-multicheck">
              <?php apply_filters('mm365_dropdown_services', []); ?>
              <option value="other" id="other_services">Other</option>
            </select>
            <br />
            <input type="text" placeholder="Others (Separate using commas)" class="form-control"
              id="other_services_input" name="other_services">
          </div>

          <div class="col-lg-4"
            data-intro="Industry to get the service or product. Ex: Janitorial services to IT company">
            <label for="">Industry</label>
            <select name="industry[]" id="industry" class="form-control mm365-multicheck" multiple>
              <?php apply_filters('mm365_dropdown_industries', []); ?>
              <option value="other" id="other_industry">Other</option>
            </select>
            <br />
            <input placeholder="Others (Separate using commas)" type="text" class="form-control"
              id="other_industry_input" name="other_industry">
          </div>
          <div class="col-lg-4" data-intro="Minority classification of the company which the buyer prefers">
            <label for="">Minority classification</label>
            <select required data-parsley-errors-container=".minority_categoryError" name="minority_category"
              id="minority_category" class="form-control mm365-single">
              <option value="all">-Select-</option>
              <option value="all">All</option>
              <?php apply_filters('mm365_dropdown_minoritycategory', []); ?>
            </select>
            <div class="minority_categoryError"></div>
          </div>
        </div>




        <!-- Location  -->
        <div class="form-row form-group">
          <div class="col"><label>Location where the services or products are required</label></div>
        </div>


        <div class="form-row form-group grouped-fields"
          data-intro="Location where the buyer wants the services or products to">


          <!-- v1.6 on wards | Servicable needed in -->
          <div class="col-lg-3">
            <label for="">Countries</label>
            <select name="service_required_countries[]" id=""
              class="serviceable-countries form-control mm365-multicheck" multiple
              data-parsley-errors-container=".countryError">
              <option value="">-Select-</option>
              <?php
              apply_filters('mm365_dropdown_countries', [])
                ?>
            </select>
            <div class="countryError"></div>
          </div>
          <div class="col-12 d-block d-sm-none pbo-30"></div>
          <div class="col-lg-3">
            <label for="">States</label>
            <select name="service_required_states[]" id="" class="serviceable-states form-control mm365-multicheck"
              multiple data-parsley-errors-container=".stateError">
              <option value="">-Select-</option>

            </select>
            <div class="stateError"></div>
          </div>
          <!-- Servicable location ends -->

        </div>


        <!-- F3 -->
        <section id="mr-advanced-block">

          <div class="form-row form-group">
            <div class="col-lg-4"
              data-intro="Filter out buyers who preferred a specific number of employees in matching company">
              <label for="">Number of employees</label>
              <select name="number_of_employees" id="" class="form-control mm365-single">
                <option value="">-Select-</option>
                <option>
                  < 20</option>
                <option>20 to 50</option>
                <option>50 to 100</option>
                <option>100 to 200</option>
                <option>200 to 500</option>
                <option>500 to 1000</option>
                <option>1000+</option>

              </select>
            </div>
            <div class="col-12 d-block d-sm-none pbo-30"></div>
            <div class="col-lg-4" data-intro="Filter out buyers who preferred a specific size of company">
              <label for="">Size of company</label>
              <select name="size_of_company" id="" class="form-control mm365-single">
                <option value="">-Select-</option>
                <option>
                  <$100,000 
                </option>
                <option>$100,000 - $500,000</option>
                <option>$500,000 - $1M</option>
                <option>$1M - $5M</option>
                <option>$5M - $50M</option>
                <option>$50M - $200M</option>
                <option>$200M - $500M</option>
                <option>$500M - $1B</option>
                <option>$1B+</option>

              </select>
            </div>
            <div class="col-12 d-block d-sm-none pbo-30"></div>
            <div class="col-lg-4"
              data-intro="If buyers preferred  specific certification, use this filter to identify such requests">
              <label for="">Industry Certifications</label>
              <select name="certifications[]" id="certifications" class="form-control mm365-multicheck" multiple>
                <?php
                apply_filters('mm365_dropdown_certifications', [])
                  ?>
                <option value="other" id="other_certification">Other</option>
              </select>
              <input type="text" placeholder="Others (Separate using commas)" class="form-control"
                id="other_certification_input" name="other_certification">
            </div>
          </div>


          <!-- f4 -->
          <div class="form-row form-group">
            <div class="col-lg-4"
              data-intro="If buyers preferred  specific NAICS codes, use this filter to identify such requests">
              <label for="">NAICS code<br /><small>Please enter only one NAICS code per row</small></label>
              <section class="naics-codes">
                <div class="form-row  form-group">
                  <div class="col">
                    <input class="form-control mr-naic" type="number" min="10" max="999999" name="naics_codes[]">
                  </div>
                  <div class="col-2 d-flex  align-items-end naics-codes-btn"><a href="#"
                      class="add-naics-code plus-btn">+</a></div>
                </div>
              </section>
              <section class="naics-codes-dynamic"></section>
              <a class="external_link" target="_blank" href="https://www.naics.com/search/"><span>Search for NAICS
                  code</span> &nbsp;<img src="<?php echo get_template_directory_uri() ?>/assets/images/share.svg"
                  alt=""></a>

            </div>
            <div class="col-lg-4"
              data-intro="If buyers preferred a suppliers looking for specific international assisstance from council. Use this filter to identify such requests">
              <label for="">Looking for international assistance<br /><small>International assistance offered by
                  council</small></label>
              <select name="international_assistance_looking_for[]" id="international_assistance" multiple
                class="form-control mm365-multicheck">
                <option value="">-Select-</option>
                <?php
                apply_filters('mm365_dropdown_internationalassistance', [])
                  ?>
              </select>
            </div>
            <div class="col-lg-4" data-intro="Filter match requests submitted by the buyers from a specific council">
              <label id="councilFilter_label" for="councilFilter">Council<br /><small>Select council</small></label>
              <select id="councilFilter" name="council_filter" class="form-control">
                <option value="">All Councils</option>
                <?php
                apply_filters('mm365_dropdown_councils', [])
                  ?>
              </select>
            </div>
          </div>
        </section>
        <div class="pto-20 pbo-20 text-left font-weight-bold">
          <a href="#" id="expand-mr-block" data-expandblock="mr-report">+ More Options</a>
        </div>


      </section>

      <div class="form-row mto-30">
        <div class="col-lg-12 text-right">
          <?php wp_nonce_field('mm365-sa-report-matchrequests', '_matchrequestsnonce_sa'); ?>
          <input type="hidden" name="action" value="mm365_generate_report_matchrequests">
          <button data-intro="Download report with all data in XLS format." name="download-sbt" id="download-report-mr"
            class="btn btn-primary">
            <?php _e('Download Report', 'mm365') ?>
          </button>
          <button data-intro="View the report on screen with limited data" name="view-sbt"
            id="matchrequests-view-report-filtered"
            data-redirect="<?php echo add_query_arg('_wpnonce', wp_create_nonce('avr_matchrequests_filtered'), site_url() . '/admin-view-report-matchrequests-filtered') ?>"
            class="btn btn-primary">
            <?php _e('View Report', 'mm365') ?>
          </button>
        </div>
      </div>


    </form>


    <!-- Panel ends -->
  </div>
</div>

<?php
get_footer();
<?php
/**
 * Template Name: Reports - Company Registartion
 *
 */


$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

if(isset($_POST['company_country']) ){
  apply_filters('mm365_admin_filteredreports_companies',1);
}

if (isset($_COOKIE['report_generate_status_cmp'])) {
  $status = $_COOKIE['report_generate_status_cmp'];
} else
  $status = NULL;

if (isset($_COOKIE['report_generate_status_cmp'])) {
  unset($_COOKIE['report_generate_status_cmp']);
  setcookie('report_generate_status_cmp', null, -1, '/');
}

get_header();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>

  </div>
  <div class="dashboard-content-panel">

    <h2 class="heading-large pbo-20">Company Registration Report</h2>

    <?php if ($status != NULL and $status == 'err'): ?>
      <div id="ajax-warnings">
        <div class="alert alert-danger" role="alert">
          <h4 class="alert-heading">Sorry no records found!</h4>
          <p>Please try with new set of parameters</p>
        </div>
      </div>
    <?php endif; ?>


    <!-- Report Generation Form -->
    <form method="post" id="mm365_generate_report_comapny"
      action="#" data-parsley-validate
      enctype="multipart/form-data">
      <!-- <form method="post" id="mm365_generate_report_comapny" action="#"  data-parsley-validate enctype="multipart/form-data" > -->
      <section class="company_preview"
        data-intro="Generate (View or Download) list of companies and use the parameters to filter out the results">

        <div class="form-row form-group">
          <!-- <span>*</span> -->
          <div class="col-lg-4" data-intro="Select the date range.">
            <label for="from_date">From date</label>
            <input class="form-control from_date_ur" type="text" name="from_date"
              data-parsley-errors-container=".frmdateError">
            <span class="calendar-icon"></span>
            <div class="frmdateError"></div>
          </div>
          <div class="col-12 d-block d-sm-none pbo-30"></div>
          <div class="col-lg-4"
            data-intro="The date values are checked based on the company registration date. The maximum duration between From date and To date is one year">
            <label for="to_date">To date</label>
            <input id="secondRangeInputUR" class="form-control to_date_ur" type="text" name="to_date"
              data-parsley-errors-container=".todateError">
            <span class="calendar-icon"></span>
            <div class="todateError"></div>
          </div>
          <div class="col-lg-4" data-intro="Filter out companies associated with a specific council">
            <label id="councilFilter_label" for="councilFilter">Council</label>
            <select id="councilFilter" name="council_filter" class="form-control">
              <option value="">All Councils</option>
              <?php
              apply_filters('mm365_dropdown_councils', array());
              ?>
            </select>
          </div>
        </div>
        <!-- <div class="form-row form-group">
          <div class="col-lg-12"><label><small>*Maximum duration between From date and To date is limited to 1 year</small></label></div>
      </div> -->

        <!-- F2 -->

        <div class="form-row form-group">
          <div class="col-lg-4" data-intro="Services offered by the companies. You can select multiple items">
            <label for="">Company services</label>
            <select name="services[]" id="services" multiple class="form-control mm365-multicheck">
              <?php
              apply_filters('mm365_dropdown_services', array());
              ?>
              <option value="other" id="other_services">Other</option>
            </select>
            <br />
            <input placeholder="Others (Separate using commas)" type="text" class="form-control"
              id="other_services_input" name="other_services">
          </div>
          <div class="col-lg-4"
            data-intro="Industries which the companies are offering their services to. You can select multiple items">
            <label for="">Industry</label>
            <select name="industry[]" id="industry" class="form-control mm365-multicheck" multiple>
              <?php
              apply_filters('mm365_dropdown_industries', array());
              ?>
              <option value="other" id="other_industry">Other</option>
            </select>
            <br />
            <input placeholder="Others (Separate using commas)" type="text" class="form-control"
              id="other_industry_input" name="other_industry">
          </div>
          <div class="col-lg-4" data-intro="Filter out buyer or supplier companies">
            <label for="">Service type</label>
            <select name="service_type" id="service_type" class="form-control mm365-single"
              data-parsley-errors-container=".stypError">
              <option value="">-Select-</option>
              <option value="buyer">Buyer</option>
              <option value="seller">Supplier</option>
            </select>
            <div class="stypError"></div>
          </div>
        </div>




        <!-- Location  -->
        <div class="form-row form-group">
          <div class="col-lg-12">Location of the company</div>
        </div>
        <div class="form-row form-group grouped-fields"
          data-intro="If you want to get the list of companies from a specific city , state or country. Use this filter">
          <div class="col-lg-4">
            <label for="">Country</label>
            <select name="company_country" id="" class="country form-control mm365-single" data-listingmode="with_all">
              <option value="">-Select-</option>
              <option value="all">All</option>
              <?php
              apply_filters('mm365_dropdown_countries', ['233'])
                ?>
            </select>
          </div>
          <div class="col-12 d-block d-sm-none pbo-30"></div>
          <div class="col-lg-4">
            <label for="">State</label>
            <select name="company_state" id="" class="state form-control mm365-single" data-listingmode="with_all">
              <option value="">-Select-</option>
              <option value="all">All</option>
              <?php
              apply_filters('mm365_dropdown_states', 233, array(), TRUE, FALSE);
              ?>
            </select>
          </div>
          <div class="col-12 d-block d-sm-none pbo-30"></div>
          <div class="col-lg-4">
            <label for="">City</label>
            <select name="company_city" id="" class="city form-control mm365-single" data-listingmode="with_all">
              <option value="">-Select-</option>
              <option value="all">All</option>
            </select>
          </div>
        </div>
        <!-- F3 -->
        <div class="form-row form-group">
          <div class="col-lg-4" data-intro="Number of employees in company">
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
          <div class="col-lg-4" data-intro="Size of the company based on business">
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
          <div class="col-lg-4" data-intro="Certifications aquired by the company.">
            <label for="">Industry Certifications</label>
            <select name="certifications[]" id="certifications" class="form-control mm365-multicheck" multiple>
              <?php
              apply_filters('mm365_dropdown_certifications', array());
              ?>
              <option value="other" id="other_certification">Other</option>
            </select>
            <span class="multiselect-icon"></span>
            <br />
            <input placeholder="Others (Separate using commas)" type="text" class="form-control"
              id="other_certification_input" name="other_certification">
          </div>
        </div>


        <!-- f4 -->
        <div class="form-row form-group">
          <div class="col-lg-4" data-intro="Filter out companies based on their NAICS codes">
            <label for="">NAICS code<br /><small>Please enter only one NAICS code per row</small></label>
            <section class="naics-codes">
              <div class="form-row  form-group">
                <div class="col">
                  <input class="form-control rep-naic" type="number" min="10" max="999999" name="naics_codes[]">
                </div>
                <div class="col-2 d-flex align-items-end naics-codes-btn"><a href="#"
                    class="add-naics-code plus-btn">+</a></div>
              </div>
            </section>
            <section class="naics-codes-dynamic"></section>
            <a class="external_link" target="_blank" href="https://www.naics.com/search/"><span>Search for NAICS
                code</span> &nbsp;<img src="<?php echo get_template_directory_uri() ?>/assets/images/share.svg"
                alt=""></a>
          </div>
          <div class="col-lg-4" id="intassi-block"
            data-intro="Filter out companies which are looking for international assistance from council">
            <label for="">International Assistance from Council<br /><small>Companies looking for</small></label>
            <select name="international_assistance[]" id="international_assistance" multiple
              class="form-control mm365-multicheck">
              <option value="">-Select-</option>
              <?php
              apply_filters('mm365_dropdown_internationalassistance', array());
              ?>
            </select>
          </div>

          <div class="col-lg-4" id="mc-block"
            data-intro="Minority classification of the company which the buyer prefers">
            <label for="">Minority classification<br /><small>Filter owner ethinicity</small></label>
            <select required data-parsley-errors-container=".minority_categoryError" name="minority_category"
              id="minority_category" class="form-control mm365-single">
              <option value="all">-Select-</option>
              <option value="all">All</option>
              <?php
                apply_filters('mm365_dropdown_minoritycategory', NULL);
              ?>
            </select>
            <div class="minority_categoryError"></div>
          </div>

        </div>

      </section>

      <div class="form-row mto-30">
        <div class="col-lg-12 text-right">
          <?php //wp_nonce_field('mm365-report-company','_companyreportnonce'); ?>
          <input type="hidden" name="action" value="mm365-generate-report-companyreg">
          <button  data-intro="Download report with all data in XLS format." name="download-sbt" id="download-report"
            class="btn btn-primary">
            <?php esc_html_e('Download Report', 'mm365') ?>
          </button>
          <button data-intro="View the report on screen with limited data" name="view-sbt"
            id="company-view-report-filtered"
            data-redirect="<?php echo add_query_arg('_wpnonce', wp_create_nonce('avr_company_filtered'), site_url() . '/admin-view-report-company-filtered') ?>"
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
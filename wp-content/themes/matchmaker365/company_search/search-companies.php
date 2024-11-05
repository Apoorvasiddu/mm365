<?php
/**
 * Template Name: SA - Search Companies
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['council_manager','mmsdc_manager']);

if (!empty($_POST)):
  $keyword = $_POST['search_company'];
  $council = $_POST['company_council'];
  $service_type = $_POST['service_type'];

  $services = (isset($_POST['services'])) ? $_POST['services'] : array();
  $industry = (isset($_POST['industry'])) ? $_POST['industry'] : array();
  $certifications = (isset($_POST['certifications'])) ? $_POST['certifications'] : array();

  $company_city = (isset($_POST['company_city'])) ? $_POST['company_city'] : NULL;
  $company_state = (isset($_POST['company_state'])) ? $_POST['company_state'] : NULL;
  $company_country = (isset($_POST['company_country'])) ? $_POST['company_country'] : NULL;

  $additional_filters = array(
    'services' => $services,
    'minority_category' => $_POST['minority_category'],
    'city' => $company_city,
    'state' => $company_state,
    'country' => $company_country,
    'search_employees' => $_POST['number_of_employees'],
    'search_companysize' => $_POST['size_of_company'],
    'industry' => $industry,
    'certifications' => $certifications,
    'naics_codes' => $_POST['naics_codes']
  );

  //$searchCompanyClass->find_and_download_companies($keyword, $council, $service_type, $additional_filters);
  apply_filters('mm365_search_and_download_companies', $keyword, $council, $service_type, $additional_filters);
endif;

get_header();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>

  </div>
  <div class="dashboard-content-panel">


    <section class="row admin-dash-filter">
      <div class="col-12">
        <h3 class="heading-large">Find Companies</h3>
      </div>
    </section>

    <!-- Search and list companies  -->
    <section class="company_preview">

      <form method="post" id="mm365_find_companies" action="" data-parsley-validate enctype="multipart/form-data">
        <div class="form-row form-group"
          data-intro="Search and find the company. Please note that the search input is looking for company names only">
          <div class="col-lg-5">
            <label for="">Find Company</label>
            <input placeholder="" class="form-control" type="text" required name="search_company" minlength="3">
          </div>
          <?php
          $user_council_id = apply_filters('mm365_helper_get_usercouncil', $user->ID);
          if ($user_council_id == ''): ?>
            <div class="col-lg-2" data-intro="Council which the company is associated with">

              <label for="">Council</label>
              <select name="company_council" id="company_council" class="form-control">
                <option value="">-Select-</option>
                <?php
                apply_filters('mm365_dropdown_councils', NULL);
                ?>
              </select>
            </div>
          <?php else: ?>
            <input type="hidden" name="company_council" value="<?php echo esc_html($user_council_id); ?>">
          <?php endif; ?>
          <div class="col-lg-2" data-intro="">
            <label for="">Service type</label>
            <select name="service_type" id="service_type" class="form-control mm365-single"
              data-parsley-errors-container=".stypError">
              <option value="">-Select-</option>
              <option value="buyer">Buyer</option>
              <option value="seller">Supplier</option>
            </select>
            <div class="stypError"></div>
          </div>
          <div class="col-lg-3" id="mc-block">

            <label for="">Minority classification</label>
            <select data-parsley-errors-container=".minority_categoryError" name="minority_category"
              id="minority_category" class="form-control mm365-single">
              <option value="">-Select-</option>
              <option value="">All</option>
              <?php apply_filters('mm365_dropdown_minoritycategory',NULL); ?>
            </select>

          </div>
        </div>

        <div class="form-row">
          <div class="col-lg-12"><label>Location of the company</label></div>
        </div>
        <div class="form-row form-group grouped-fields"
          data-intro="If you want to get the list of companies from a specific city , state or country. Use this filter">
          <div class="col-lg-4">
            <label for="">Country</label>
            <select name="company_country" id="" class="country form-control mm365-single" data-listingmode="with_all">
              <option value="">-Select-</option>
              <option value="all">All</option>
              <?php
              $country_list = apply_filters('mm365_helper_countries_list',1);
              foreach ($country_list as $key => $value) {
                if ($value->id == '233'):
                  $default_country = "selected";
                else:
                  $default_country = '';
                endif;
                echo "<option " . $default_country . " value='" . $value->id . "' >" . $value->name . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-12 d-block d-sm-none pbo-30"></div>
          <div class="col-lg-4">
            <label for="">State</label>
            <?php $states_list = apply_filters('mm365_helper_states_list',233); ?>
            <select name="company_state" id="" class="state form-control mm365-single" data-listingmode="with_all">
              <option value="">-Select-</option>
              <option value="all">All</option>
              <?php
              foreach ($states_list as $key => $value) {
                echo "<option  value='" . $value->id . "' >" . $value->name . "</option>";
              } ?>
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


        <div class="form-row form-group ">
          <div class="col-lg-3" data-intro="Services offered by the companies. You can select multiple items">
            <label for="">Company products or services</label>
            <select name="services[]" id="services" multiple class="form-control mm365-multicheck">
              <?php apply_filters('mm365_dropdown_services',array()); ?>
            </select>
          </div>

          <div class="col-lg-3"
            data-intro="Industries which the companies are offering their services to. You can select multiple items">
            <label for="">Industry</label>
            <select name="industry[]" id="industry" class="form-control mm365-multicheck" multiple>
            <?php apply_filters('mm365_dropdown_industries',array()); ?>
            </select>

          </div>

          <div class="col-lg-3" data-intro="Number of employees in company">
            <label for="">Number of employees</label>
            <select name="number_of_employees" id="" class="form-control mm365-single">
              <option value="">-Select-</option>
              <option>
                < 20
              </option>
              <option>20 to 50</option>
              <option>50 to 100</option>
              <option>100 to 200</option>
              <option>200 to 500</option>
              <option>500 to 1000</option>
              <option>1000+</option>
            </select>
          </div>

          <div class="col-12 d-block d-sm-none pbo-30"></div>
          <div class="col-lg-3" data-intro="Size of the company based on business">
            <label for="">Size of company</label>
            <select name="size_of_company" id="" class="form-control mm365-single">
              <option value="">-Select-</option>
             <option>Less than $100,000</option>
              <option>$100,000 - $500,000</option>
              <option>$500,000 - $1,000,000</option>
              <option>$1M- $5M</option>
              <option>$5M-$10M</option>
               <option>$10M-$25M</option>
               <option>$25M-$50M</option>
              <option>Greater than $50,000,000</option>
            </select>
          </div>

        </div>

        <div class="form-row form-group">
          <!-- <div class="col-lg-3" data-intro="Filter out companies based on their NAICS codes">
            <label for="">NAICS code</label>
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
          </div> -->
          <div id="basicSearchFields" class="col-lg-4">
                                    <label for="">Find NAICS codes<br/>
                                          </label>
                                   
                                    <section  class="naics-codes">
                                          <div  class="form-row">
                                                <div class="col naics-input-box">
                                                      <input class="form-control naics-input " type="text" min="10"
                                                            max="999999" name="naics_code" placeholder="search and select naics code" >
                                                            <p class="naic-info"></p>
                                                            <div class="naic-suggested"></div>
                                                </div>
                                          </div>
                                    </section>
                                   <label><small>Search by category name or NAICS code then click the list to add</small></label>
                                   <a class="external_link" target="_blank" href="https://www.naics.com/search/"><span>Search for NAICS code</span> &nbsp;<img src="<?php echo get_template_directory_uri() ?>/assets/images/share.svg" alt=""></a>
                              </div>
                              <div class="col-lg-3">
                              <label for="">Selected NAICS codes<br/>
                                         
                                    </label>
                                    <section class="naics-codes-dynamic"></section>
                                     
                              </div>


          <div class="col-lg-3" data-intro="Certifications aquired by the company.">
            <label for="">Industry Certifications</label>
            <select name="certifications[]" id="certifications" class="form-control mm365-multicheck" multiple>
            <?php apply_filters('mm365_dropdown_certifications',array()); ?>
            </select>
          </div>

        </div>


        <div class="form-row form-group">
          <div class="col-lg-4" data-intro="Initiate search">
            <button id="search_company" type="submit" class="btn btn-primary">
              <?php _e('Search', 'mm365') ?>
            </button>
            <button id="download_search_company" type="submit" class="btn btn-primary">
              <?php _e('Download', 'mm365') ?>
            </button>
          </div>
        </div>
      </form>

      <!-- Ajax Push data here -->
      <div id="companies-data-table" class="pto-30">

      </div>

    </section>


  </div>
</div>

<?php
get_footer();
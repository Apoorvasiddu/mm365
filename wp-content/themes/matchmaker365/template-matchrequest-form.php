<?php
/**
 * Template Name: Request For Match - Form Only
 *
 */


$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['business_user']);

//Check if user has active registration else redirect
do_action('mm365_helper_check_companyregistration', 'register-your-company');



//Check for drafted items
$args = array(
      'author' => $user->ID,
      'post_type' => 'mm365_matchrequests',
      'post_status' => 'draft',
      'posts_per_page' => 1,
      'orderby' => 'title',
);
$drafted_items = new WP_Query($args);
while ($drafted_items->have_posts()):
      $drafted_items->the_post();
      if ($drafted_items):
            foreach ($drafted_items as $df_items) {
                  if (get_post_status(get_the_ID()) == 'draft') {
                        $users_published_company = '';
                        $users_company = get_the_ID();
                        $redirect = site_url() . '/edit-matchrequest?mr_id=' . get_the_ID() . '&mr_state=draft';
                        wp_redirect(add_query_arg('_wpnonce', wp_create_nonce('match_request'), $redirect));
                        exit;
                  }
            }
      endif;
endwhile;
get_header();
?>


<div class="dashboard">
      <div class="dashboard-navigation-panel">
            <!-- Users Menu -->
            <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
      </div>
      <div class="dashboard-content-panel">

            <h1 class="heading-large pbo-10">Request for Match </h1>
            <!-- Request for match form -->
            <form method="post" id="mm365_request_for_match" action="#" data-parsley-validate
                  enctype="multipart/form-data">
                  <section class="company_preview">

                  <div class="form-row form-group">
                              <div id="basicSearchFields" class="col-lg-5">
                                    <label for="">Find NAICS codes<br/>
                                          <small>Search by category name or NAICS code then click the list to add</small>
                                    </label>
                                    
                                    <section  class="naics-codes">
                                          <div  class="form-row  form-group">
                                                <div class="col naics-input-box">
                                                      <input class="form-control naics-input" type="text" min="10"
                                                            max="999999" name="naics_code" placeholder="search and select naics code" >
                                                            <p class="naic-info"></p>
                                                            <div class="naic-suggested"></div>
                                                </div>
                                          </div>
                                    </section>
                                      <a class="external_link" target="_blank" href="https://www.naics.com/search/"><span>Search for NAICS code</span> &nbsp;<img src="<?php echo get_template_directory_uri() ?>/assets/images/share.svg" alt=""></a>
                              </div>
                              <div class="col-lg-5">
                              <label for="">Selected NAICS codes<span>*</span><br/>
                                         
                                    </label>
                                    <section class="naics-codes-dynamic"></section>
                                  
                              </div>
                              
                        </div>

                        <div class="form-row form-group"
                              data-intro="Type the keywords to find a matching company for the service you are looking for. <br/>Ex: janitorial, plastic parts  ">
                              <div class="col">
                                    <label for="">Search the services or products you are looking for<span>*</span>
                                          <br /><small>For multiple keywords, please separate them using commas. Maximum
                                                of
                                                <?php
                                                echo apply_filters('mm365_helper_get_themeoption', 'mm365_mrform_keyword_count', NULL);
                                                ?> keywords are allowed, each keyword cannot exceed more than
                                                <?php
                                                echo apply_filters('mm365_helper_get_themeoption', 'mm365_mrform_keyword_charlimit', NULL);

                                                ?> characters.

                                                <?php if (get_post_meta($_COOKIE['active_company_id'], 'mm365_service_type', true) == 'seller'): ?>
                                                      <div>
                                                            <div class="attention-message">Please make sure you are not
                                                                  creating a match request for the goods/services you provide
                                                            </div>
                                                      </div>
                                                <?php endif; ?>
                                          </small>
                                    </label>
                                    <textarea name="services_looking_for" required id="" cols="30" rows="2"
                                          class="form-control" maxlength="260" placeholder="Type keywords"></textarea>
                              </div>
                        </div>


                        <div class="form-row form-group">
                                          <div class="col-lg-3">
                                                <label for="">Annual sales in dollars</label>
                                                <select name="size_of_company" id="mr_size_of_company"
                                                      class="form-control mm365-single">
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
                                          <div class="col-12 d-block d-sm-none pbo-30"></div>
                                          <div class="col-lg-3">
                                                <label for="">Number of employees</label>
                                                <select name="number_of_employees" id="mr_number_of_employees"
                                                      class="form-control mm365-single">
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
                                    </div>
 

                        <!-- Location -->
                        <div class="form-row form-group">
                              <div class="col-lg-12">
                                    <label for="">Choose the location where the services or products are needed</label>
                              </div>
                        </div>
                        <div class="form-row form-group grouped-fields"
                              data-intro="Select the location where you want the company to provide the service/product">


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
                                    <select name="service_required_states[]" id=""
                                          class="serviceable-states form-control mm365-multicheck" multiple
                                          data-parsley-errors-container=".stateError">
                                          <option value="">-Select-</option>
                                          <?php //$mm365_helper->states_dropdown(233,array(),TRUE); ?>
                                    </select>
                                    <div class="stateError"></div>
                              </div>
                              <!-- Servicable location ends -->


                        </div>



                        <!-- Location -->
                        <section class="accordion mbo-30"
                              data-intro="Advanced search parameters like NAICS Code, Company services  that can used to improve match accuracy">
                              <!-- <a class="expand">
                                    <div class="right-arrow">+</div>
                                    <div>
                                          <h3>Advanced Search</h3>
                                          <p>Please click here to view additional search parameters</p>
                                    </div>
                              </a> -->
                              <section class="accordion-fold">
                                    <!-- Advanced search block -->
                                    <div class="form-row form-group">
                                          <div class="col-lg-4">
                                                <label for="">Services or products required
                                                </label>
                                                <select name="services[]" id="services"
                                                      data-parsley-required-message="Must select at least one" multiple
                                                      data-parsley-errors-container=".servError"
                                                      class="form-control mm365-multicheck">
                                                      <?php
                                                      apply_filters('mm365_dropdown_services', array());
                                                      ?>
                                                      <option value="other" id="other_services">Other</option>
                                                </select>
                                                <div class="servError"></div>
                                                <input type="text" placeholder="Others (Separate using commas)"
                                                      class="form-control" id="other_services_input"
                                                      name="other_services">
                                          </div>

                                          <div class="col-lg-4">
                                                <label for="">Industry</label>
                                                <select name="industry[]" id="industry"
                                                      data-parsley-required-message="Must select at least one"
                                                      data-parsley-errors-container=".industryError" multiple
                                                      class="form-control mm365-multicheck">
                                                      <?php
                                                      apply_filters('mm365_dropdown_industries', array());
                                                      ?>
                                                      <option value="other" id="other_industry">Other</option>
                                                </select>
                                                <div class="industryError"></div>
                                                <input type="text" placeholder="Others (Separate using commas)"
                                                      class="form-control" id="other_industry_input"
                                                      name="other_industry">
                                          </div>
                                          <div class="col-lg-4">
                                                <label for="">Minority classification</label>
                                                <select name="mr_mbe_category[]" id="mr_mbe_category"
                                                      data-parsley-errors-container=".mrmbecateError" multiple
                                                      class="form-control mm365-multicheck">
                                                      <?php
                                                      apply_filters('mm365_dropdown_minoritycategory', NULL);
                                                      ?>
                                                </select>
                                                <span class="multiselect-icon"></span>
                                                <div class="mrmbecateError"></div>
                                          </div>
                                    </div>


                                    <div class="form-row form-group">
                                          <div class="col-lg-4">
                                                <label for="">Required Industry Certifications</label>
                                                <select name="certifications[]" id="certifications"
                                                      class="form-control mm365-multicheck" multiple>
                                                      <?php
                                                      apply_filters('mm365_dropdown_certifications', array());
                                                      ?>
                                                      <option value="other" id="other_certification">Other</option>
                                                </select>
                                                <span class="multiselect-icon"></span>
                                                <br />
                                                <input type="text" placeholder="Others (Separate using commas)"
                                                      class="form-control" id="other_certification_input"
                                                      name="other_certification">
                                          </div>
                                          <div class="col-lg-4">
                                                <label for="">Looking for international assisitance </label>
                                                <select name="looking_for[]" multiple id="looking_for"
                                                      class="form-control mm365-multicheck">
                                                      <option value="">-Select-</option>
                                                      <?php
                                                      apply_filters('mm365_dropdown_internationalassistance', array());
                                                      ?>
                                                </select>
                                          </div>
                                          <div class="col-12 d-block d-sm-none pbo-30"></div>

                                    </div>

                                    <div class="form-row form-group">

                                          <div class="col-lg-6">

                                          <?php
                                          //v3.0 auto approval is standard
                                                $company_id = esc_html($_COOKIE['active_company_id']);
                                                $approval_stat = get_post_meta($company_id, 'mm365_approval_required_feature', true);

                                                if ($approval_stat == 'enabled' OR $approval_stat==NULL OR  $approval_stat!='disabled') {
                                                       $btmode = 'Find Matches';
                                                ?>
                                                <label for="">Council approval required</label><br />
                                                <input type="radio" name="approval_required" id=""
                                                            value="yes">&nbsp;Yes&nbsp;&nbsp;
                                                <input type="radio" checked name="approval_required" id=""
                                                    value="no">&nbsp;No
                                                <?php
                                                } else {
                                                      $btmode = 'Submit for Approval';
                                                      ?>
                                                      <input type="hidden" name="approval_required" id="" value="yes">
                                                      <?php
                                                 } 
                                          ?>

                                                <?php //$btmode = 'Find Matches' ?>
                                                <!-- <label for="">Council approval required</label><br />
                                                      <input type="radio" name="approval_required" id=""
                                                            value="yes">&nbsp;Yes&nbsp;&nbsp;
                                                      <input type="radio" checked name="approval_required" id=""
                                                            value="no">&nbsp;No -->

                                          </div>
                                    </div>
                                    <!-- Advanced search block ends -->
                              </section>
                        </section>

                  </section>
                  <div class="form-row pto-30">
                        <div class="col text-right">
                              <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                              <input type="hidden" name="current_user" id="current_user"
                                    value="<?php echo esc_html($user->ID); ?>" />

                              <input type="hidden" name="requester_company_id" id="requester_company_id"
                                    value="<?php echo esc_html($_COOKIE['active_company_id']); ?>" />
                              <input type="hidden" name="requester_council_id" id="requester_council_id"
                                    value="<?php echo esc_html(get_post_meta($_COOKIE['active_company_id'], 'mm365_company_council', true)); ?>" />

                              <input type="hidden" name="advanced_search" id="advanced_search" value="true" />
                              <input type="hidden" name="submitted" id="submitted" value="true" />
                              <button id="mr_submit_btn" data-btnmode="<?php esc_html_e($btmode); ?>" type="submit"
                                    class="btn btn-primary">
                                    <?php esc_html_e($btmode); ?>
                              </button>
                        </div>
                  </div>


            </form>


      </div>
</div>

<?php
get_footer();
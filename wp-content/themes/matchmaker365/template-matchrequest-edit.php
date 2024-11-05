<?php
/**
 * Template Name: Match Request Edit
 *
 */


$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole',['business_user']);


$nonce = $_REQUEST['_wpnonce'];
if (!wp_verify_nonce($nonce, 'match_request')) {
  die(__('Security check', 'mm365'));
}

get_header();
$mrid = $_REQUEST['mr_id'];

get_header();
//Check for drafted items
$args = array(
  'author' => $user->ID,
  'p' => $mrid,
  'post_type' => 'mm365_matchrequests',
  'fields' => 'ids',
  'posts_per_page' => 1,
  'orderby' => 'title',
);
$drafted_items = new WP_Query($args);
while ($drafted_items->have_posts()):
  $drafted_items->the_post();
  if ($drafted_items):

    $status = get_post_meta($mrid, 'mm365_matchrequest_status', true);
    $approval_time_flag = get_post_meta($mrid, 'mm365_matched_companies_approved_time', true);

    ?>



    <div class="dashboard">
      <div class="dashboard-navigation-panel">
        <!-- Users Menu -->
        <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
      </div>
      <div class="dashboard-content-panel">

        <h1 class="heading-large pbo-10">Edit Request for Match Details</h1>
        <section class="company_preview"
          data-intro="Match request details and parameters can be edited and resubmitted until the request is approved by MatchMaker365">
          <?php if ($status != 'approved'): ?>

            <?php
            if (get_post_status($mrid) == 'publish' and $approval_time_flag == '') {
              $form_class = '_active';
            } else
              $form_class = '';
            ?>

            <!-- Request for match form -->
            <form method="post" id="mm365_request_for_match_update<?php echo $form_class; ?>" action="#" data-parsley-validate
              enctype="multipart/form-data">
              <input id="mr_id" type="hidden" name="mr_id" value="<?php echo $mrid; ?>">

              <div class="form-row form-group">
              <div id="basicSearchFields" class="col-lg-6">
                      <label for="">NAICS code
                        <small>&nbsp;&nbsp;&nbsp;Please enter only one NAICS code per row</small>
                      </label>
                      <section class="naics-codes">
                        <div class="form-row  form-group">
                          <div class="col naics-input-box">
                            <input id="mr_naics" class="form-control naics-input" type="number" name="naics_codes[]">
                            <p class="naic-info"></p>
                          </div>
                          <div class="col-2 d-flex  align-items-end naics-codes-btn"><a href="#"
                              class="add-naics-code plus-btn">+</a></div>
                        </div>
                      </section>
                      <section class="naics-codes-dynamic">

                        <?php foreach ((get_post_meta($mrid, 'mm365_naics_codes')) as $key => $value) { ?>
                          <section class="naics_remove">
                            <div class="form-row  form-group">
                              <div class="col">
                                <input id="mr_naics" class="form-control" type="number" min="10" max="999999"
                                  name="naics_codes[]" value="<?php echo $value; ?>">
                              </div>
                              <div class="col-2 d-flex align-items-end naics-codes-btn"><a href="#"
                                  class="remove-naics-code plus-btn">-</a></div>
                            </div>
                          </section>
                        <?php } ?>

                      </section>
                      <a class="external_link" target="_blank" href="https://www.naics.com/search/"><span>Search for NAICS
                          code</span> &nbsp;<img src="<?php echo get_template_directory_uri() ?>/assets/images/share.svg"
                          alt=""></a>
                    </div>
              </div>

              <div class="form-row form-group">
                <div class="col-lg-12">
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
                  <textarea name="services_looking_for" required id="mr_services_looking_for" cols="30" rows="4"
                    class="form-control"
                    placeholder="Type keywords"><?php echo get_post_meta($mrid, 'mm365_services_details', true) ?></textarea>
                </div>
              </div>

              <div class="form-row form-group">
                    <div class="col-lg-3">
                      <label for="">Annual sales in dollars</label>
                      <select name="size_of_company" id="mr_size_of_company" class="form-control mm365-single">
                        <option value="">-Select-</option>
                        <?php
                        $current_size_of_company = get_post_meta($mrid, 'mm365_size_of_company', true);
                        $size = get_post_meta($mrid, 'mm365_size_of_company', true);
                        if ($size == '&lt;$100,000'):
                          $current_size_of_company = "<$100,000";
                        else:
                          $current_size_of_company = $size;
                        endif;


                        $size_of_company = array(
                          '<$100,000',
                          '$100,000 - $500,000',
                          '$500,000 - $1M',
                          '$1M - $5M',
                          '$5M - $50M',
                          '$50M - $200M',
                          '$200M - $500M',
                          '$500M - $1B',
                          '$1B+'
                        );
                        foreach ($size_of_company as $key) {
                          if ($current_size_of_company == $key) {
                            echo "<option selected>" . $key . "</option>";
                          } else {
                            echo "<option>" . $key . "</option>";
                          }
                        }
                        ?>

                      </select>
                    </div>
                    <div class="col-12 d-block d-sm-none pbo-30"></div>
                    <div class="col-lg-3">
                      <label for="">Number of employees</label>
                      <select name="number_of_employees" id="mr_number_of_employees" class="form-control mm365-single">
                        <option value="">-Select-</option>
                        <?php
                        $current_number_of_employees = get_post_meta($mrid, 'mm365_number_of_employees', true);
                        $employee_count = get_post_meta($mrid, 'mm365_number_of_employees', true);
                        if ($employee_count == '&lt; 20'):
                          $current_number_of_employees = "< 20";
                        else:
                          $current_number_of_employees = $employee_count;
                        endif;

                        $number_of_employees = array("< 20", "20 to 50", "50 to 100", "100 to 200", "200 to 500", "500 to 1000", "1000+");
                        foreach ($number_of_employees as $key) {
                          if ($current_number_of_employees == $key) {
                            echo "<option selected>" . $key . "</option>";
                          } else {
                            echo "<option>" . $key . "</option>";
                          }
                        }
                        ?>
                      </select>
                    </div>

                  </div>


              <!-- Location -->
              <div class="form-row form-group">
                <div class="col-lg-12">
                  <label for="">Choose the location where the services or products are needed</label>
                </div>
              </div>
              <div class="form-row form-group grouped-fields">




                <!-- v1.6 on wards | Servicable needed in -->
                <?php
                $current_countries = get_post_meta($mrid, 'mm365_service_needed_country');
                $current_states = get_post_meta($mrid, 'mm365_service_needed_state');
                ?>
                <div class="col-lg-3">
                  <label for="">Countries </label>
                  <select name="service_required_countries[]" id="serviceable-countries"
                    class="serviceable-countries form-control mm365-multicheck" multiple
                    data-parsley-errors-container=".countryError">
                    <option value="">-Select-</option>
                    <?php
                      apply_filters('mm365_dropdown_countries', $current_countries)
                    ?>
                  </select>
                  <div class="countryError"></div>
                </div>
                <div class="col-12 d-block d-sm-none pbo-30"></div>
                <div class="col-lg-3">
                  <label for="">States</label>
                  <select name="service_required_states[]" id="serviceable-states"
                    class="serviceable-states form-control mm365-multicheck" multiple
                    data-parsley-errors-container=".stateError">
                    <option value="">-Select-</option>
                    <?php
                    apply_filters('mm365_company_preload_serviceable_states', $current_countries, $current_states);
                    ?>
                  </select>
                  <div class="stateError"></div>
                </div>
                <!-- Servicable location ends -->



              </div>
              <!-- Location -->
              <section class="accordion mbo-30">
                <!-- <a class="expand">
                  <div class="right-arrow">+</div>
                  <div>
                    <h3>Advanced Search</h3>
                    <p>Please click here to view additional search parameters</p>
                  </div>
                </a> -->
                <section class="accordion-fold-" >
                  <!-- Advanced search block -->
                  <div class="form-row form-group">
                    <div class="col-lg-4">
                      <label for="">Services or products required</label>
                      <select name="services[]" id="services" data-parsley-errors-container=".servError" multiple
                        data-parsley-required-message="Must select at least one" class="form-control mm365-multicheck">
                        <?php
                        $current_services = (get_post_meta($mrid, 'mm365_services_looking_for'));
                        apply_filters('mm365_dropdown_services', $current_services);
                        ?>


                        <option value="other" <?php if (in_array("other", $current_services)) {
                          echo "selected";
                        } ?>
                          id="other_services">Other</option>
                      </select>
                      <span class="multiselect-icon"></span>
                      <div class="servError"></div>
                      <br />
                      <input id="other_services_input" type="text" class="form-control" name="other_services"
                        placeholder="Others (Separate using commas)" value="<?php if (in_array("other", $current_services)):
                          $other_pos = (array_search('other', $current_services));
                          echo implode(",", array_slice($current_services, $other_pos + 1));
                        endif; ?>">
                    </div>
                    <div class="col-lg-4">
                      <label for="">Industry</label>
                      <?php
                      $current_services_industry = (get_post_meta($mrid, 'mm365_services_industry'));
                      ?>
                      <select name="industry[]" id="industry" data-parsley-errors-container=".industryError"
                        data-parsley-required-message="Must select at least one" multiple
                        class="form-control mm365-multicheck">
                        <?php
                        apply_filters('mm365_dropdown_industries', $current_services_industry);
                        ?>
                        <option value="other" <?php if (in_array("other", $current_services_industry)) {
                          echo "selected";
                        } ?>
                          id="other_industry">Other</option>
                      </select>
                      <span class="multiselect-icon"></span>
                      <div class="industryError"></div>
                      <br />
                      <input type="text" placeholder="Others (Separate using commas)" class="form-control"
                        id="other_industry_input" name="other_industry" data-parsley-errors-container=".oth_industryError"
                        value="<?php if (in_array("other", $current_services_industry)):
                          $other_pos = (array_search('other', $current_services_industry));
                          echo implode(",", array_slice($current_services_industry, $other_pos + 1));
                        endif; ?>">
                      <div class="oth_industryError"></div>

                    </div>
                    <div class="col-lg-4">
                      <label for="">Minority classification</label>
                      <?php
                      $current_mbe_categories = (get_post_meta($mrid, 'mm365_mr_mbe_category'));
                      if ($current_mbe_categories == '') {
                        $current_mbe_categories = array("");
                      }
                      ?>
                      <select name="mr_mbe_category[]" id="mr_mbe_category" data-parsley-errors-container=".mrmbecateError"
                        multiple class="form-control mm365-multicheck">
                        <?php
                        apply_filters('mm365_dropdown_minoritycategory', $current_mbe_categories);
                        ?>
                      </select>
                      <span class="multiselect-icon"></span>
                      <div class="mrmbecateError"></div>
                    </div>
                  </div>


                  <div class="form-row form-group">
                    <div class="col-lg-4">
                      <label for="">Required Industry Certifications</label>

                      <?php
                      $current_certifications = (get_post_meta($mrid, 'mm365_certifications'));
                      $certifications = array();
                      ?>
                      <select name="certifications[]" id="certifications" class="form-control mm365-multicheck" multiple>
                        <?php 
                         apply_filters('mm365_dropdown_certifications',$current_certifications);
                        ?>
                        <option <?php if (in_array("other", $current_certifications)) {
                          echo "selected";
                        } ?> value="other"
                          id="other_certification">Other</option>
                      </select>
                      <span class="multiselect-icon"></span>
                      <br />
                      <input type="text" placeholder="Others (Separate using commas)" class="form-control"
                        id="other_certification_input" value="<?php
                        if (in_array("other", $current_certifications)):
                          $other_pos = (array_search('other', $current_certifications));
                          echo implode(",", array_slice($current_certifications, $other_pos + 1));
                        endif; ?>" name="other_certification">

                    </div>
                    <div class="col-lg-4">
                      <label for="">Looking for international assistance</label>
                      <select name="looking_for[]" id="looking_for" multiple class="form-control mm365-multicheck">
                        <option value="">-Select-</option>
                        <?php
                        $current_intassi = get_post_meta($mrid, 'mm365_match_intassi_lookingfor');
                        apply_filters('mm365_dropdown_internationalassistance', $current_intassi);
                        ?>
                      </select>
                    </div>

                  </div>



                  <!-- newly added -->
                  <div class="form-row form-group">

                    <div class="col-12 d-block d-sm-none pbo-30"></div>
                    <div class="col-lg-6">
                      <?php
                      $company_id = esc_html($_COOKIE['active_company_id']);
                      $approval_stat = get_post_meta($company_id, 'mm365_approval_required_feature', true);
                      if ($approval_stat == 'disabled'):
                        $btmode = 'Find Matches';

                        ?>
                        <label for="">Council approval required</label><br />
                        <?php $approval_mode = get_post_meta($mrid, 'mm365_approval_type', true); ?>
                        <input type="radio" <?php if ($approval_mode == 'yes')
                          echo ' checked '; ?> name="approval_required"
                          id="approval_required" value="yes">&nbsp;Yes&nbsp;&nbsp;
                        <input type="radio" <?php if ($approval_mode == 'no')
                          echo ' checked '; ?> name="approval_required"
                          id="approval_required" value="no">&nbsp;No
                      <?php else:
                        $btmode = 'Submit for Approval';
                        ?>
                        <input type="hidden" name="approval_required" id="approval_required_hidden" value="yes">
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Advanced search block ends -->
                </section>
              </section>



              <div class="form-row">
                <div class="col-lg-12 text-right">
                  <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                  <input type="hidden" name="advanced_search" id="advanced_search" value="false" />
                  <button id="mr_submit_btn" data-btnmode="<?php esc_html_e($btmode); ?>" type="submit"
                    class="btn btn-primary"><?php esc_html_e($btmode); ?></button>
                </div>
              </div>
            </form>
          <?php else: ?>
            <h4 class="text-center">This match request is no longer editable.</h4>
          <?php endif; ?>
        </section>
        <!-- Request for match form -->
      <?php
  endif;
endwhile;
wp_reset_postdata();
?>

  </div>
</div>

<?php
get_footer();
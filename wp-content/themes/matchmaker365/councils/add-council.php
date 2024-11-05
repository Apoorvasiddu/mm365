<?php
/**
 * Template Name: SA - Add Council
 *
 */
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>
  <div class="dashboard-content-panel">

    <h1 class="heading-large pbo-10">Add Council</h1>
    <!-- Edit Block -->
    <form method="post" id="mm365_add_council" action="#" data-parsley-validate enctype="multipart/form-data">
      <section class="company_preview">
        <div class="form-row form-group">
          <div class="col-9" data-intro="Full name of the council">
            <label for="">Council name<span>*</span></label>
            <input placeholder="Please add council's full name" class="form-control" type="text" required
              name="council_name" id="council_name" pattern="/^[a-zA-Z-\s']+$/" minlength="4" value="">
          </div>
          <div class="col-3" data-intro="Short name of the council. In capital letters. For ex: MMSDC">
            <label for="">Short name<span>*</span></label>
            <input placeholder="Please enter short name" class="form-control" type="text" required
              name="council_short_name" id="council_short_name" pattern="/^[a-zA-Z-\s]+$/" minlength="4" value="">
          </div>
        </div>
        <!-- Dropzone -->
        <div class="form-row form-group"
          data-intro="Council logo should be in PNG format without any background color. Since the image is being displayed on the darker background (footer) the text inside logo should be white or a bright color for readability">
          <div class="col">
            <label for="">Council logo<span>*</span>
              <br /><small>Please upload background less PNG image with resolution 203x135px. Ensure that the texts in
                logo are light colored. Files size should not exceed 1MB</small>
            </label>
            <br />
            <div class="dropzonee" id="council-dropzone" data-existing="">
              <div class="dz-message needsclick" for="files">Drag & drop Council's logo.<br />
                <small>Please upload background less PNG image with resolution 203x135px. Ensure that the texts in logo
                  are light colored. Files size should not exceed 1MB</small>
                <div class="fallback">
                  <input class="form-control-file" type="file" id="wp_custom_attachment" name="files" />
                </div>
              </div>
            </div>
            <ul class="parsley-errors-list filled" id="validate-council-logo" aria-hidden="false">
              <li class="parsley-required capability-statemets-error">This value is required.</li>
            </ul>
          </div>
        </div>
        <!-- Dropzone -->
        <div class="form-row form-group">
          <div class="col-12" data-intro="A short summary about council. The content will be displayed on the footer">
            <label for="">Short Description<span>*</span></label>
            <textarea placeholder="Short description" class="form-control" type="text" required
              name="council_description" id="council_description" value=""></textarea>
          </div>
        </div>
        <div class="form-row form-group" data-intro="Council's office address and location">
          <div class="col-lg-3">
            <label for="">Address<span>*</span></label>
            <textarea placeholder="Please enter council address" required class="form-control" name="council_address"
              id="" cols="30" rows="1"></textarea>
          </div>
          <div class="col-lg-3">
            <label for="">Country<span>*</span></label>
            <select required name="council_country" id="council_country" class="country form-control mm365-single"
              data-parsley-errors-container=".countryError">
              <option value="">-Select-</option>
              <?php
              $country_list = apply_filters('mm365_helper_countries_list', 1);
              foreach ($country_list as $key => $value) {
                //if($current_country[0] == $value->id): $default_country = "selected"; else: $default_country = ''; endif;
                echo "<option   value='" . $value->id . "' >" . $value->name . "</option>";
              }
              ?>
            </select>
            <div class="countryError"></div>
          </div>
          <div class="col-lg-3">
            <label for="">State<span>*</span></label>
            <?php //$states_list   = mm365_states_list(233); ?>
            <select required name="council_state" id="council_state" class="state form-control mm365-single"
              data-parsley-errors-container=".stateError">
              <option value="">-Select-</option>
            </select>
            <div class="stateError"></div>
          </div>
          <div class="col-lg-3">
            <label for="">City<span>*</span></label>
            <select required name="council_city" id="council_city" class="city form-control mm365-single"
              data-parsley-errors-container=".cityError">
              <option value="">-Select-</option>

            </select>
            <div class="cityError"></div>
          </div>
        </div>
        <div class="form-row form-group" data-intro="Council's contact information">
          <div class="col-lg-3">
            <label for="">ZIP<span>*</span></label>
            <input class="form-control" type="text" placeholder="Please enter ZIP code" required
              data-parsley-required-message="Please enter a valid zip code." name="council_zip_code" pattern="[0-9+\-]+"
              data-parsley-length="[4, 15]" value=""
              data-parsley-length-message="The ZIP code should be 4 to 15 digits long">
          </div>
          <div class="col-lg-3">
            <label for="">Contact person<span>*</span></label>
            <input placeholder="Please enter your full name" class="form-control" pattern="[a-zA-Z\s]+" minlength="4"
              type="text" required name="council_contact_person" value="">
          </div>
          <div class="col-lg-3">
            <label for="">Email<span>*</span></label>
            <input class="form-control" placeholder="Please enter a valid email" type="email" required
              name="council_email" data-parsley-type-message="This value should be a valid email ID." value="">
          </div>
          <div class="col-lg-3">
            <label for="">Phone<span>*</span></label>
            <input placeholder="E.g. 555 555 5555" class="form-control" type="text" required pattern="[0-9+()\s]+"
              data-parsley-length="[6, 15]" name="council_phone"
              data-parsley-length-message="The phone number should be 6 to 15 digits long" value="">
          </div>
        </div>
        <div class="form-row form-group">
          <div class="col-lg-3" data-intro="Council's website">
            <label for="">Council website</label>
            <input placeholder="E.g. www.example.com" class="form-control" type="text" name="website"
              data-parsley-type='url' data-parsley-type-message="This value seems to be invalid." value="">
          </div>
          <div class="col-lg-3" data-intro="Google / Bing Map link to council's office location. ">
            <label for="">MAP Link</label>
            <input placeholder="E.g. https://goo.gl/maps/R5NdpkPzR1bhPx1n9" class="form-control" type="text"
              name="map_link" data-parsley-type='url' data-parsley-type-message="This value seems to be invalid."
              value="">
          </div>
        </div>
        <div class="form-row">
          <div class="col-lg-12">
            <h6>Social Media</h6>
            <hr />
          </div>
        </div>
        <div class="form-row form-group"
          data-intro="Coucil's social media profile. Please add the social media handle, DO NOT paste entire url. For example if the council's facebook page is https://facebook.com/mmsdc just type mmsdc in the input for facebook">
          <div class="col-lg-2">
            <label for="">Facebook</label>
            <input placeholder="E.g. mmsdc" class="form-control" type="text" name="facebook_id" value="">
          </div>
          <div class="col-lg-2">
            <label for="">Instagram</label>
            <input placeholder="E.g. mmsdc" class="form-control" type="text" name="instagram_id" value="">
          </div>
          <div class="col-lg-2">
            <label for="">Twitter</label>
            <input placeholder="E.g. mmsdc" class="form-control" type="text" name="twitter_id" value="">
          </div>
          <div class="col-lg-2">
            <label for="">LinkedIn</label>
            <input placeholder="E.g. in/mmsdc" class="form-control" type="text" name="linkedin_id" value="">
          </div>
          <div class="col-lg-2">
            <label for="">Youtube</label>
            <input placeholder="E.g. mmsdc" class="form-control" type="text" name="youtube_id" value="">
          </div>
          <div class="col-lg-2">
            <label for="">Flickr</label>
            <input placeholder="E.g. mmsdc" class="form-control" type="text" name="flickr_id" value="">
          </div>
        </div>


        <div class="form-row">
          <div class="col-lg-12">
            <h5>Permissions</h5>
            <hr />
          </div>
        </div>

        <div class="form-row form-group">
          <div class="col-lg-2"
            data-intro="If the toggle switch is enabled, the council mangers of this council can approve match requests.">
            <label for="">Match Approval Privilege</label>
            <label class="toggle-control">
              <input class="toggler" type="checkbox" name="permission_mr" id="permission_mr">
              <span class="control"></span>
            </label>
          </div>
          <div class="col-lg-3"
            data-intro="If pre-select is enabled, match request result set will be pre selected with council manager's council">
            <label for="">Pre-select council in match results</label>
            <label class="toggle-control">
              <input class="toggler" type="checkbox" name="preselect_mr" id="preselect_mr">
              <span class="control"></span>
            </label>
          </div>

          <div class="col-lg-3"
            data-intro="All the MBEs in this council requires an active subscription to appear in match results">
            <label for="">MBEs require active subscription to match</label>
            <label class="toggle-control">
              <input class="toggler" type="checkbox" name="mbe_require_subscription" id="mbe_require_subscription">
              <span class="control"></span>
            </label>
          </div>

        </div>

        <div class="form-row mto-30">
          <div class="col-lg-12">
            <h5>Other Settings</h5>
            <hr />
          </div>
        </div>
        <div class="form-row form-group">
          <div class="col-lg-2"
            data-intro="If the toggle switch is enabled, the council mangers of this council can approve match requests.">
            <label for="">Hide from footer</label>
            <label class="toggle-control">
              <input class="toggler" type="checkbox" name="hide_from_footer" id="hide_from_footer">
              <span class="control"></span>
            </label>
          </div>
          <div class="col-lg-2"
            data-intro="If the toggle switch is enabled, the council mangers of this council can approve match requests.">
            <label for="">Category</label>
            <select name="council_category" id="" class="form-control mm365-single">
              <option value="founding">Founding Councils</option>
              <option value="affiliates">Affiliate Councils</option>
              <option value="global">Global Initiative</option>
              <option value="mbda">MBDA Centers</option>
              <option value="other">Other Councils</option>
            </select>
          </div>
        </div>

        <div class="form-row pto-10">
          <div class="col text-right">
            <input type="hidden" name="current_user" id="current_user" value="<?php echo esc_html($user->ID); ?>" />
            <input type="hidden" id="after_success_redirect" name="after_success_redirect"
              value="<?php echo esc_url(site_url() . "/list-councils"); ?>">
            <button id="sa_council_add" type="submit" class="btn btn-primary">
              <?php _e('Add Council', 'mm365') ?>
            </button>
          </div>
        </div>


      </section>
    </form>


  </div><!-- dash panel -->
</div><!--dash -->

<?php
get_footer();
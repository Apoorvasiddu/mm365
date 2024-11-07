<?php
/**
 * Template Name: CM - Council Add Conference
 *
 */
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['council_manager']);

get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>
  <div class="dashboard-content-panel">

    <h1 class="heading-large pbo-10">New Conference</h1>
    <!-- Edit Block -->

    <section class="company_preview">

      <form method="post" id="mm365_council_add_conference" action="#" data-parsley-validate
        enctype="multipart/form-data">
        <div class="row">
          <!-- Left part -->
          <div class="col-6" data-intro="Full name of the council">

            <div class="form-row form-group">
              <label for="">Title<span>*</span></label>
              <input placeholder="Please add requirement title" class="form-control" type="text" required
                name="conference_title" id="conference_title" pattern="/^[a-zA-Z-\s\.']+$/" minlength="4" value="">
            </div>

            <div class="form-row form-group">
              <div class="col-12">
                <label for="">Requirement Description<span>*</span></label>
                <textarea placeholder="Short description" class="form-control" type="text" required
                  name="conference_description" id="conference_description"
                  data-parsley-errors-container=".conferece_description_error"></textarea>
                <div class="conferece_description_error"></div>
              </div>
            </div>
            <!--
            <div class="form-row form-group">
              <div class="col-6">
                <label for="">Approximate Value of Business<span>*</span></label>
                <input placeholder="Ball park estimate of the deal" class="form-control" type="text" required
                  name="business_value" id="business_value" value="">
              </div>
              <div class="col-6">
                <label for="">Keywords<span>*</span></label>
                <input placeholder="Keywords to find matching suppliers" class="form-control" type="text" required
                  name="keywords" id="keywords" value="">
              </div>
            </div>
           
            <div class="form-row form-group">
              <div class="col-6">
                <label for="">Conference Scope<span>*</span></label><br />
                <div class="form-check-inline">
                  <input type="radio" class="form-check-input" checked name="conf_scope" value="council">Council Level
                </div>
                <div class="form-check-inline">
                  <input type="radio" class="form-check-input" name="conf_scope" value="national">National Level
                </div>
              </div>
            </div>
 -->
            <div class="pto-30">
              <h5>Participating Buyer(s)</h5>
              <hr>
            </div>
            <div class="form-row form-group">
              <div class="col-12">
                <label for="">Select Buyer(s)<span>*</span></label>
                <select name="participating_buyers[]" id="participating_buyers" multiple required class="form-control"
                  data-parsley-errors-container=".participating_buyers_error"></select>
                <div class="participating_buyers_error"></div>
              </div>
            </div>

          </div>
          <!-- left part ends -->

          <!-- Right part -->
          <div class="col-6" data-intro="">
            <div class="form-row form-group">

              <label for="">Time Zone<span>*</span>
              </label><br />
              <select name="timezone" id="timezone" class="form-control mm365-single">
                <?php echo apply_filters('mm365_meetings_list_timezones', 1) ?>
              </select>

            </div>
            <div class="form-row form-group">
              <div class="row">
                <div class="col-sm-4">
                  <label for="">Conference Date <span>*</span></label>
                  <input type="text" name="conference_date" id="" class="meeting_date_1 form-control " required
                    placeholder="" data-parsley-errors-container=".meeting_date_error">
                  <div class="meeting_date_error"></div>
                </div>
                <div class="col-6 col-sm-4">
                  <label for="">From<span>*</span></label>
                  <input type="text" name="conference_starttime" id="" class="from_time_1 form-control" required
                    placeholder="" data-parsley-errors-container=".from_time_1_error">
                  <div class="from_time_1_error"></div>
                </div>
                <div class="col-6 col-sm-4">
                  <label for="">To<span>*</span></label>
                  <input type="text" name="conference_endtime" id="" class="to_time_1 form-control" required
                    placeholder="" data-parsley-errors-container=".to_time_1_error">
                  <div class="to_time_1_error"></div>
                </div>

              </div>
            </div>

            <div class="form-row form-group">
              <div class="col-6">
                <label for="">Conference Venue<span>*</span></label>
                <input type="text" name="meeting_venue" required id="" class=" form-control " required placeholder="">
              </div>
              <div class="col-6">
                <label for="">Map link</label>
                <input type="url" name="map_link" id="" class=" form-control " placeholder="">
              </div>
            </div>
            <!--
            <div class="form-row form-group">
              <div class="col-12">
                <label for="">Event Amenities<span>*</span></label>
                <textarea rows="4" placeholder="Details of amenities, food beverages parking etc" class="form-control"
                  type="text" required name="event_amneties" id="" value=""></textarea>
              </div>
            </div> -->

            <div class="form-row form-group">
              <!--
              <div class="col-5">
                <label for="">Maximum Occupancy <span>*</span></label>
                <span class="d-flex gap-3">
                  <input type="number" name="maximum_deligates" min="2" step="2" max="500" class=" form-control "
                    required placeholder="" data-parsley-errors-container=".maximum_deligates_error"
                    style="width:40%; margin-right:10px"> Deligates
                </span>
                <div class="maximum_deligates_error"></div>
              </div>
            -->
              <div class="col-5">
                <label for="">Registration Closing Date <span>*</span></label>
                <input type="text" name="registration_closing_date" id="" class="form-control registration_closing_date"
                  required placeholder="" data-parsley-errors-container=".registration_closing_date_error">
                <div class="registartion_closing_date_error"></div>
              </div>
            </div>

            <div class="pto-30">
              <h5>Organizing Team</h5>
              <hr>
            </div>
            <div class="form-row form-group">
              <div class="col-12">
                <label for="">Council Managers<span>*</span></label>
                <select name="fellow_council_managers[]" id="fellow_council_managers" multiple required
                  class="form-control" data-parsley-errors-container=".fellow_council_managers_error"></select>
                <div class="fellow_council_managers_error"></div>
              </div>
            </div>
            <div class="form-row form-group">
              <div class="col-6">
                <label for="">Primary Contact Person<span>*</span></label>
                <input type="text" name="primary_contact_person" id="" class=" form-control " required placeholder="">
              </div>
              <div class="col-6">
                <label for="">Phone Number<span>*</span></label>
                <input type="text" name="contact_phone_number" id="" class=" form-control " required
                  pattern="[0-9+()\s]+" data-parsley-length="[6, 15]" placeholder="">
              </div>
            </div>

          </div>
          <!-- Right part ends-->

        </div>



        <div class="form-row pto-10">
          <div class="col text-right">
            <input type="hidden" name="current_user" id="current_user" value="<?php echo esc_html($user->ID); ?>" />
            <input type="hidden" id="after_success_redirect" name="after_success_redirect"
              value="<?php echo esc_url(site_url() . ""); ?>">
            <button id="council_create_conf" type="submit" class="btn btn-primary">
              <?php _e('Save & Preview', 'mm365') ?>
            </button>
          </div>
        </div>
      </form>

      <div id="preview_conference">
        <!-- Conference details preview - AJAX response -->
      </div>

    </section>



  </div><!-- dash panel -->
</div><!--dash -->

<?php
get_footer();
<?php
/**
 * Template Name: CM - Council Edit Conference
 *
 */
$user = wp_get_current_user();

$conf_id = $_REQUEST['conf_id'];

//Ensure post author is editing
$author_id = get_post_field('post_author', $conf_id);
if ($author_id != $user->ID) {
    die(__('Unauthorised action', 'mm365'));
}


//Restrict the page access 
// $mm365_helper->check_loginandrole('council_manager');
// $meetings     = new mm365_meetings();



$title = get_the_title($conf_id);
$content = get_post_field('post_content', $conf_id);
$conf_date = get_post_meta($conf_id, 'conf_date', true);
$conf_start_time = get_post_meta($conf_id, 'conf_start_time', true);
$conf_end_time = get_post_meta($conf_id, 'conf_end_time', true);
$conf_timezone = get_post_meta($conf_id, 'conf_timezone', true);
$conf_business_value = get_post_meta($conf_id, 'conf_business_value', true);
$conf_keywords = get_post_meta($conf_id, 'conf_keywords', true);
$conf_venue = get_post_meta($conf_id, 'conf_venue', true);
$conf_map_link = get_post_meta($conf_id, 'conf_map_link', true);
$conf_event_amneties = get_post_meta($conf_id, 'conf_event_amneties', true);
$conf_maximum_deligates = get_post_meta($conf_id, 'conf_maximum_deligates', true);
$conf_registration_closing_date = get_post_meta($conf_id, 'conf_registration_closing_date', true);

$conf_primary_contact_person = get_post_meta($conf_id, 'conf_primary_contact_person', true);
$conf_contact_phone_number = get_post_meta($conf_id, 'conf_contact_phone_number', true);

$conf_scope = get_post_meta($conf_id, 'conf_scope', true);

get_header();
?>

<div class="dashboard">
    <div class="dashboard-navigation-panel">
        <!-- Users Menu -->
        <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
    </div>
    <div class="dashboard-content-panel">

        <h1 class="heading-large pbo-10">Edit Conference</h1>
        <!-- Edit Block -->

        <section class="company_preview">

            <form method="post" id="mm365_council_update_conference" action="#" data-parsley-validate
                enctype="multipart/form-data">
                <div class="row">
                    <!-- Left part -->
                    <div class="col-6" data-intro="Full name of the council">

                        <div class="form-row form-group">
                            <label for="">Title<span>*</span></label>
                            <input placeholder="Please add requirement title" class="form-control" type="text" required
                                name="conference_title" id="conference_title" pattern="/^[a-zA-Z-\s']+$/" minlength="4"
                                value="<?php
                                echo $title;
                                ?>">
                        </div>

                        <div class="form-row form-group">
                            <div class="col-12">
                                <label for="">Requirement Description<span>*</span></label>
                                <textarea placeholder="Short description" class="form-control" type="text" required
                                    name="conference_description" id="conference_description"
                                    data-parsley-errors-container=".conferece_description_error">
            <?php
            echo $content;
            ?>
          </textarea>
                                <div class="conferece_description_error"></div>
                            </div>
                        </div>
                        <!--
        <div class="form-row form-group">
          <div class="col-6">
            <label for="">Approximate Value of Business<span>*</span></label>
            <input placeholder="Ball park estimate of the deal" class="form-control"  type="text" required name="business_value" id="business_value"  value="<?php
            echo $conf_business_value;
            ?>
            "> 
          </div>
          <div class="col-6">
            <label for="">Keywords<span>*</span></label>
            <input placeholder="Keywords to find matching suppliers" class="form-control"  type="text" required name="keywords" id="keywords"  value="<?php
            echo $conf_keywords;
            ?>"> 
          </div>
        </div>
 
        <div class="form-row form-group">
            <div class="col-6">
                <label for="">Conference Scope<span>*</span></label><br/>
                
                <div class="form-check-inline">
                    <input <?php if ($conf_scope == 'council'): ?>checked<?php endif; ?>  type="radio" class="form-check-input"  name="conf_scope" value="council"> Council Level
                </div>    
                <div class="form-check-inline">  
                    <input <?php if ($conf_scope == 'national'): ?>checked<?php endif; ?> type="radio" class="form-check-input" name="conf_scope" value="national"> National Level
                </div>  
            </div>
        </div>
 -->
                        <!-- MY CODE -->
                        <div class="form-row form-group">
                            <div id="basicSearchFields" class="col-lg-5">
                                <label for="">Find NAICS codes<br />
                                    <small>Search by category name or NAICS code then click the list to add</small>
                                </label>

                                <section class="naics-codes">
                                    <div class="form-row  form-group">
                                        <div class="col naics-input-box">
                                            <input class="form-control naics-input" type="text" min="10" max="999999"
                                                name="naics_code" placeholder="search and select naics code">
                                            <p class="naic-info"></p>
                                            <div class="naic-suggested"></div>
                                        </div>
                                    </div>
                                </section>
                                <a class="external_link" target="_blank"
                                    href="https://www.naics.com/search/"><span>Search for NAICS code</span> &nbsp;<img
                                        src="<?php echo get_template_directory_uri() ?>/assets/images/share.svg"
                                        alt=""></a>
                            </div>
                            <div class="col-lg-5">
                                <label for="">Selected NAICS codes<span>*</span><br /></label>
                                <section class="naics-codes-dynamic">
                                    <?php foreach ((get_post_meta($conf_id, 'conf_naics_codes')) as $key => $value) { ?>
                                        <section class="naics_remove">
                                            <div class="form-row  form-group">
                                                <div class="col">
                                                    <input id="mr_naics" class="form-control" type="number" readonly
                                                        min="10" max="999999" name="naics_codes[]"
                                                        value="<?php echo $value; ?>">
                                                </div>
                                                <div class="col-2 d-flex align-items-end naics-codes-btn"><a href="#"
                                                        class="remove-naics-code plus-btn">-</a></div>
                                            </div>
                                        </section>
                                    <?php } ?>
                                </section>

                            </div>

                        </div>
                        <!-- end my code -->
                        <div class="pto-30">
                            <h5>Participating Buyer(s)</h5>
                            <hr>
                        </div>
                        <div class="form-row form-group">
                            <div class="col-12">
                                <label for="">Select Buyer(s)<span>*</span></label>
                                <select name="participating_buyers[]" data-conf_id="<?php echo esc_html($conf_id); ?>"
                                    id="participating_buyers" multiple required class="form-control"
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
                                    <input type="text" name="conference_date" id="" class="meeting_date_1 form-control "
                                        required placeholder="" data-parsley-errors-container=".meeting_date_error"
                                        value="<?php
                                        echo $conf_date;
                                        ?>">
                                    <div class="meeting_date_error"></div>
                                </div>

                                <div class="col-sm-4">
                                    <label for="">From<span>*</span></label>
                                    <input type="text" name="conference_starttime" id=""
                                        class="from_time_1 form-control" required placeholder=""
                                        data-parsley-errors-container=".from_time_1_error" value="<?php
                                        echo $conf_start_time;
                                        ?>">
                                    <div class="from_time_1_error"></div>
                                </div>

                                <div class="col-sm-4">
                                    <label for="">To<span>*</span></label>
                                    <input type="text" name="conference_endtime" id="" class="to_time_1 form-control"
                                        required placeholder="" data-parsley-errors-container=".to_time_1_error" value="<?php
                                        echo $conf_end_time;
                                        ?>">
                                    <div class="to_time_1_error"></div>
                                </div>

                            </div>
                        </div>
                        <div class="form-row form-group">
                            <div class="col-6">
                                <label for="">Conference Venue<span>*</span></label>
                                <input type="text" name="meeting_venue" required id="" class=" form-control " required
                                    placeholder="" data-parsley-errors-container=".first_choice_error" value="<?php
                                    echo $conf_venue;
                                    ?>">
                            </div>
                            <div class="col-6">
                                <label for="">Map link</label>
                                <input type="url" name="map_link" id="" class=" form-control " placeholder=""
                                    data-parsley-errors-container=".first_choice_error" value="<?php
                                    echo $conf_map_link;
                                    ?>">
                            </div>
                        </div>
                        <!--
        <div class="form-row form-group">
            <div class="col-12">
                  <label for="">Event Amenities<span>*</span></label>
                  <textarea rows="4" placeholder="Details of amenities, food beverages parking etc" class="form-control"  type="text" required name="event_amneties" id="" value=""><?php
                  echo $conf_event_amneties; ?>
                  </textarea>
            </div>
        </div>
      -->
                        <div class="form-row form-group">
                            <!--
            <div class="col-5">
                        <label for="">Maximum Occupancy <span>*</span></label>
                        <span class="d-flex gap-3">
                          <input type="number" name="maximum_deligates" id="" class=" form-control"  min="2" step="2" max="500" required placeholder="" data-parsley-errors-container=".maximum_deligates_error" style="width:40%; margin-right:10px" value="<?php echo $conf_maximum_deligates; ?>"> Deligates
                        </span>
                        <div class="maximum_deligates_error"></div>  
            </div> 
        -->
                            <div class="col-5">
                                <label for="">Registration Closing Date <span>*</span></label>
                                <input type="text" name="registration_closing_date" id=""
                                    class="form-control registration_closing_date" required placeholder=""
                                    data-parsley-errors-container=".registration_closing_date_error"
                                    value="<?php echo $conf_registration_closing_date; ?>">
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
                                <select data-conf_id="<?php echo esc_html($conf_id); ?>"
                                    name="fellow_council_managers[]" id="fellow_council_managers" multiple required
                                    class="form-control"
                                    data-parsley-errors-container=".fellow_council_managers_error"></select>
                                <div class="fellow_council_managers_error"></div>
                            </div>
                        </div>
                        <div class="form-row form-group">
                            <div class="col-6">
                                <label for="">Primary Contact Person<span>*</span></label>
                                <input type="text" name="primary_contact_person" id="" class=" form-control " required
                                    placeholder="" value="<?php echo $conf_primary_contact_person; ?>">
                            </div>
                            <div class="col-6">
                                <label for="">Phone Number<span>*</span></label>
                                <input type="text" name="contact_phone_number" id="" class=" form-control " required
                                    placeholder="" pattern="[0-9+()\s]+" data-parsley-length="[6, 15]"
                                    value="<?php echo $conf_contact_phone_number; ?>">
                            </div>
                        </div>

                    </div>
                    <!-- Right part ends-->

                </div>



                <div class="form-row pto-10">
                    <div class="col text-right">
                        <input type="hidden" name="current_user" id="current_user"
                            value="<?php echo esc_html($user->ID); ?>" />
                        <input type="hidden" name="update_conf_id" id="update_conf_id"
                            value="<?php echo esc_html($conf_id); ?>" />
                        <input type="hidden" id="after_success_redirect" name="after_success_redirect"
                            value="<?php echo esc_url(site_url() . ""); ?>">
                        <button id="council_create_conf" type="submit"
                            class="btn btn-primary"><?php _e('Update & Preview', 'mm365') ?></button>
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
<?php
/**
 * Template Name: SA - Add Super Buyer
 *
 */
$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole', ['mmsdc_manager']);
get_header();
?>

<div class="dashboard">
        <div class="dashboard-navigation-panel">
                <!-- Users Menu -->
                <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
        </div>
        <div class="dashboard-content-panel">

                <h1 class="heading-large pbo-10">Add Super Buyer</h1>
                <!-- Request for match form -->
                <form method="post" id="mm365_add_superbuyer" action="#" data-parsley-validate
                        enctype="multipart/form-data">
                        <section class="company_preview">
                                <div class="form-row form-group">
                                        <div class="col-lg-4"
                                                data-intro="Email address of the user. This email address will be used to login">
                                                <label for="">Email<span>*</span></label>
                                                <input placeholder="Please enter email" class="form-control"
                                                        type="email" required name="superbuyer_email"
                                                        id="mm365_superbuyer_email">
                                                <span class="check-icon">
                                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/red-tick.svg"
                                                                id="email-check-fail" alt="">
                                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/green-tick.svg"
                                                                id="email-check-success" alt="">
                                                </span>
                                        </div>
                                        <div class="col-lg-4"
                                                data-intro="A unique username. You can see the availability indicator next to the input field ">
                                                <label for="">Username<span>*</span></label>
                                                <input placeholder="Please enter username" class="form-control"
                                                        type="text" required name="superbuyer_username"
                                                        pattern="/^(?:[a-zA-Z0-9_@.]+)?$/" minlength="3"
                                                        id="mm365_superbuyer_username">
                                                <span class="check-icon">
                                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/red-tick.svg"
                                                                id="username-check-fail" alt="">
                                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/green-tick.svg"
                                                                id="username-check-success" alt="">
                                                </span>
                                        </div>
                                </div>
                                <div class="form-row form-group" data-intro="First name, last name and phone number">
                                        <div class="col-lg-4">
                                                <label for="">First Name<span>*</span></label>
                                                <input placeholder="" class="form-control" type="text" required
                                                        name="superbuyer_first_name" pattern="[a-zA-Z\s]+"
                                                        minlength="2">
                                        </div>
                                        <div class="col-lg-4">
                                                <label for="">Last Name<span>*</span></label>
                                                <input placeholder="" class="form-control" type="text" required
                                                        name="superbuyer_last_name" pattern="[a-zA-Z\s]+" minlength="2">
                                        </div>
                                        <div class="col-lg-4">
                                                <label for="">Phone<span>*</span></label>
                                                <input placeholder="" class="form-control" type="text" required
                                                        name="superbuyer_phone" pattern="[0-9+()\s]+"
                                                        data-parsley-length="[6, 15]"
                                                        data-parsley-length-message="The phone number should be 6 to 15 digits long">
                                        </div>
                                </div>
                                <div class="form-row form-group">
                                        <div class="col-lg-4" data-intro="Associated council.">
                                                <label for="">Council<span>*</span></label>
                                                <select name="superbuyer_council_id" id="superbuyer_council_id" required
                                                        class="form-control">
                                                        <option value="">-Select-</option>
                                                        <?php
                                                        apply_filters('mm365_dropdown_councils', NULL);
                                                        ?>
                                                </select>
                                        </div>
                                        <div class="col-lg-4" data-intro="Buyer associated">
                                                <label for="">Associated Buyers</label>
                                                <select name="associated_buyers[]" id="associated_buyers" multiple
                                                        class="form-control">

                                                </select>
                                        </div>
                                </div>
                                <div class="form-row form-group">
                                        <div class="col-lg-4"
                                                data-intro="This is user's login status. If you want to block the user from accessing the platform, change the status to INACTIVE">
                                                <label for="">Is the super buyer for an event or conference</label><br />
                                                <input type="radio" name="sb_for_event" id="" value="yes"> Yes
                                                &nbsp;&nbsp;&nbsp;<input type="radio" name="sb_for_event" id=""
                                                        value="no" checked> No
                                        </div>
                                        <div class="col-lg-4" id="conference_participating">
                                                <?php
                                                $upcomingConferences = apply_filters('mm365_offline_conferences_list', false, false);
                                                ?>
                                                <label for="">Conference Participating<span>*</span></label>
                                                <select name="associatedbuyer_upcoming_conference"
                                                        id="associatedbuyer_upcoming_conference" class="form-control">
                                                        <option value="">-Select-</option>
                                                        <optgroup label="National Conferences">
                                                        <?php foreach ($upcomingConferences as $conference) {
                                                                if($conference['scope'] == 'national'){
                                                                ?>
                                                                 
                                                                        <option value="<?php echo esc_html($conference['ID']); ?>">
                                                                                <?php echo esc_html($conference['name']); ?>
                                                                        </option>
                                                                
                                                                <?php
                                                                }
                                                        }
                                                        ?>
                                                         </optgroup>
                                                        <optgroup label="Council Conferences">
                                                                <option disabled value="">Select council</option>
                                                        </optgroup>
                                                </select>
                                        </div>

                                </div>
                                <div class="form-row pto-30">
                                        <div class="col text-right">
                                                <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                                                <input type="hidden" name="current_user" id="current_user"
                                                        value="<?php echo esc_html($user->ID); ?>" />
                                                <input type="hidden" id="after_success_redirect"
                                                        name="after_success_redirect"
                                                        value="<?php echo esc_url(site_url() . "/list-super-buyers"); ?>">
                                                <button id="sa_superbuyer_add" type="submit" class="btn btn-primary">
                                                        <?php _e('Submit', 'mm365') ?>
                                                </button>
                                        </div>
                                </div>

                        </section>
                </form>




        </div><!-- dash panel -->
</div><!--dash -->

<?php
get_footer();
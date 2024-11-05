<?php
/**
 * Template Name: SA - Add Sub Buyer
 *
 */
$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole', ['super_buyer']);
get_header();
?>

<div class="dashboard">
        <div class="dashboard-navigation-panel">
                <!-- Users Menu -->
                <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
        </div>
        <div class="dashboard-content-panel">

                <h1 class="heading-large pbo-10">Add Associated Buyer</h1>
                <!-- Request for match form -->
                <form method="post" id="mm365_add_subbuyer" action="#" data-parsley-validate
                        enctype="multipart/form-data">
                        <section class="company_preview">
                                <div class="form-row form-group">
                                        <div class="col-lg-4"
                                                data-intro="Email address of the user. This email address will be used to login">
                                                <label for="">Email<span>*</span></label>
                                                <input placeholder="Please enter email" class="form-control"
                                                        type="email" required name="associatedbuyer_email"
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
                                                        type="text" required name="associatedbuyer_username"
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
                                        <div class="col-lg-3">
                                                <label for="">First Name<span>*</span></label>
                                                <input placeholder="" class="form-control" type="text" required
                                                        name="associatedbuyer_first_name" pattern="[a-zA-Z\s]+"
                                                        minlength="2">
                                        </div>
                                        <div class="col-lg-3">
                                                <label for="">Last Name<span>*</span></label>
                                                <input placeholder="" class="form-control" type="text" required
                                                        name="associatedbuyer_last_name" pattern="[a-zA-Z\s]+" minlength="2">
                                        </div>
                                        <div class="col-lg-3">
                                                <label for="">Phone<span>*</span></label>
                                                <input placeholder="" class="form-control" type="text" required
                                                        name="associatedbuyer_phone" pattern="[0-9+()\s]+"
                                                        data-parsley-length="[6, 15]"
                                                        data-parsley-length-message="The phone number should be 6 to 15 digits long">
                                        </div>
                                        <div class="col-lg-3">
                                                <label for="">Alternative Phone<span>*</span></label>
                                                <input placeholder="" class="form-control" type="text" required
                                                        name="associatedbuyer_alt_phone" pattern="[0-9+()\s]+"
                                                        data-parsley-length="[6, 15]"
                                                        data-parsley-length-message="The phone number should be 6 to 15 digits long">
                                        </div>
                                </div>
                                <div class="form-row form-group">
                                        <div class="col-lg-3" data-intro="Associated council.">
                                                <label for="">Council Associated<span>*</span></label>
                                                <select name="associatedbuyer_council_id" id="superbuyer_council_id" required
                                                        class="form-control">
                                                        <option value="">-Select-</option>
                                                        <?php
                                                        apply_filters('mm365_dropdown_councils', NULL);
                                                        ?>
                                                </select>
                                        </div>
                                        <div  class="col-lg-2" data-intro="">
                                                <label class="control-label pto-10"  for="">For Conference<span>*</span></label><br/>
						<input type="radio" name="asb_for_conference"  value="yes"> Yes
						&nbsp;<input type="radio" name="asb_for_conference"  value="no" checked> No
                                        </div>
                                        <div id="asb_for_conference_block" class="col-lg-3" data-intro="">
                                                <?php
                                                $upcomingConferences = apply_filters('mm365_offline_conferences_list', FALSE, TRUE);
                                                ?>
                                                <label for="">Conference Participation<span>*</span></label>
                                                <select name="associatedbuyer_upcoming_conference" id="associatedbuyer_upcoming_conference" 
                                                        class="form-control">
                                                        <option value="">-Select-</option>

                                                        <?php foreach ($upcomingConferences as $conference) {
                                                                ?>
                                                                <option value="<?php echo esc_html($conference['ID']); ?>">
                                                                        <?php echo esc_html($conference['name']); ?>
                                                                </option>
                                                                <?php
                                                        }
                                                        ?>
                                                </select>
                                        </div>
                                </div>
                                <div class="form-row form-group"
                                        data-intro="Please enter the meeting agenda and notes if any.">
                                        
                                        <div class="col-lg-12">
                                                <label for="">
                                                        Corporation<span>*</span>
                                                        <br />
                                                        <small>Please enter the name of the corporation you are representing Ex: Ford USA.</small>
                                                </label><br />
                                                <input placeholder="" class="form-control" type="text" required
                                                        name="associatedbuyer_brand" pattern="[a-zA-Z\s]+" minlength="2">
                                        </div>
                                </div>
                                <!-- <div class="form-row form-group"
                                        data-intro="Please enter the meeting agenda and notes if any.">
                                        
                                        <div class="col-lg-12">
                                                <label for="">
                                                        NAICS Codes<span>*</span>
                                                        <br />
                                                        <small>Use , (comma) to sepearte the NAICS codes</small>
                                                </label><br />
                                                <input placeholder="" id="sb_naics_code" class="form-control" type="text" required
                                                        name="sb_naics_codes"  minlength="2">
                                        </div>
                                </div> -->
                                <div  class="form-row form-group">
                                        
    <div id="basicSearchFields" class="col-lg-4">
                                    <label for="">Find NAICS codes<br/>
                                          </label>
                                   
                                    <section  class="naics-codes">
                                          <div  class="form-row">
                                                <div class="col naics-input-box">
                                                      <input class="form-control naics-input" type="text" min="10"
                                                            max="999999" name="naics_code" placeholder="search and select naics code" >
                                                            <p class="naic-info"></p>
                                                            <div class="naic-suggested"></div>
                                                </div>
                                          </div>
                                    </section>
                                   <label><small>Search by category name or NAICS code then click the list to add</small></label>
                              </div>
                              <div class="col-lg-3">
                              <label for="">Selected NAICS codes<span>*</span><br/>
                                         
                                    </label>
                                    <section class="naics-codes-dynamic"></section>
                              </div>

                                </div>
                                <div class="form-row form-group"
                                        data-intro="Please enter the meeting agenda and notes if any.">
                                        
                                        <div class="col-lg-12">
                                                <label for="">
                                                Buyer Opportunities<span>*</span>
                                                        <br />
                                                        <small>Please enter the meeting agenda and notes if any.</small>
                                                </label><br />
                                                <textarea id="opportunities" name="opportunities" required
                                                        data-parsley-errors-container=".descError"></textarea>
                                                <div class="descError"></div>
                                        </div>
                                </div>
                                <div class="form-row pto-30">
                                        <div class="col text-right">
                                                <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                                                <input type="hidden" name="current_user" id="current_user"
                                                        value="<?php echo esc_html($user->ID); ?>" />
                                                <input type="hidden" id="after_success_redirect"
                                                        name="after_success_redirect"
                                                        value="<?php echo esc_url(site_url() . "/buyer-team"); ?>">
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
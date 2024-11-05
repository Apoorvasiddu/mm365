<?php
/**
 * Template Name: Schedule Meeting
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['business_user']);

//Check if user has active registration else redirect
apply_filters('mm365_helper_check_companyregistration', 'register-your-company');

$mr_id = $_REQUEST['mr_id'];
$company_id = $_REQUEST['cid'];
$nonce = $_REQUEST['_wpnonce'];

if (!wp_verify_nonce($nonce, 'schedule_meeting')) {
    die(__('Unauthorised token', 'mm365'));
}

$meeting_with_company = get_the_title($company_id);
$contact_person = get_post_meta($company_id, 'mm365_contact_person', true);
$contact_email = get_post_meta($company_id, 'mm365_company_email', true);
$alt_contact_person = get_post_meta($company_id, 'mm365_alt_contact_person', true);
$alternative_email = get_post_meta($company_id, 'mm365_alt_email', true);



//$meetings          = new mm365_meetings();
$is_schedulable = apply_filters('is_schedulable', $company_id, $mr_id);
$is_meeting_exists = count(apply_filters('mm365_meeting_status', $company_id, $mr_id, true, true));
//Move back if already scheduled
if ($is_schedulable == TRUE and $is_meeting_exists > 0):
    wp_redirect(site_url() . "/meetings-scheduled");
endif;

get_header();
?>
<div class="dashboard">
    <div class="dashboard-navigation-panel">
        <!-- Users Menu -->
        <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
    </div>
    <div class="dashboard-content-panel">
        <h1 class="heading-large pbo-10">Schedule a meeting</h1>
        <?php if ($is_schedulable == TRUE and $is_meeting_exists == 0): ?>
            <!--New meeting form -->
            <form method="post" id="mm365_create_meeting" action="#" data-parsley-validate enctype="multipart/form-data">
                <section class="company_preview">

                    <div class="form-row form-group" data-intro="Details of the supplier company">
                        <div class="col-6 col-md-5">
                            <label for="">Company Name</label><br />
                            <?php echo esc_html($meeting_with_company); ?>
                            <input type="hidden" name="meeting_with_company"
                                value="<?php echo esc_html($meeting_with_company); ?>">
                            <input type="hidden" name="meeting_with_company_id"
                                value="<?php echo esc_html($company_id); ?>">
                            <input type="hidden" name="proposed_company_id" id="requester_company_id"
                                value="<?php echo esc_html($_COOKIE['active_company_id']); ?>" />
                            <input type="hidden" name="from_match_request" id="from_match_request"
                                value="<?php echo esc_html($mr_id); ?>" />
                            <!--Proposer Council ID and attendees council id -->
                            <input type="hidden" name="mm365_proposer_council_id" id="mm365_proposer_council_id"
                                value="<?php echo esc_html(apply_filters('mm365_helper_get_usercouncil', $user->ID)); ?>" />
                            <input type="hidden" name="mm365_attendees_council_id" id="mm365_attendees_council_id"
                                value="<?php echo esc_html(get_post_meta($company_id, 'mm365_company_council', true)); ?>" />

                        </div>
                        <div class="col-6 col-md-2">
                            <label for="">Contact Person</label><br />
                            <?php echo esc_html($contact_person); ?><br />
                            <?php echo esc_html($alt_contact_person); ?>
                            <input type="hidden" name="meeting_contact_person"
                                value="<?php echo esc_html($contact_person); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="">Email</label><br />
                            <span class="text-break"><?php echo esc_html($contact_email); ?></span><br />
                            <input type="hidden" name="meeting_contact_email"
                                value="<?php echo esc_html($contact_email); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="">Alternative Email</label><br />
                            <span
                                class="text-break"><?php echo ($alternative_email != NULL) ? $alternative_email : '-'; ?></span>
                            <input type="hidden" name="meeting_contact_alt_email"
                                value="<?php echo esc_html($alternative_email); ?>">
                        </div>
                    </div>

                    <div class="form-row form-group" data-intro="A short title which explains your requirement">
                        <div class="col-7">
                            <label for="">Title<span>*</span>
                            </label>
                            <input type="text" name="meeting_title" id="" class="form-control" required
                                placeholder="Meeting title">
                        </div>
                        <div  class="col-lg-3" data-intro="">
                            <?php
                            $upcomingConferences = apply_filters('mm365_offline_conferences_list', FALSE, TRUE);
                            ?>
                            <label for="">Conference Participation<span>*</span></label>
                            <select required name="conference_participation" id="associatedbuyer_upcoming_conference"
                                class="form-control">
                                <option value="">-Select-</option>
                                <option value="not-participating">Not Participating</option>
                                <optgroup label="Upcoming conferences">
                                <?php foreach ($upcomingConferences as $conference) {
                                    ?>
                                    <option value="<?php echo esc_html($conference['ID']); ?>">
                                        <?php echo esc_html($conference['name']); ?>
                                    </option>
                                    <?php
                                }
                                ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-group"
                        data-intro="Propose preferred three dates and times when you would like to schedule the meeting. It is required to enter the First Preference.">
                        <div class="col">
                            <label for="">Meeting date & time<br />
                                <small>Propose preferred three dates and times when you would like to schedule the meeting.
                                    It is required to enter the First Preference.</small>
                            </label>
                        </div>
                    </div>

                    <div class="form-row form-group"
                        data-intro="Your timezone. If you are travelling between timezones please do choose the timezone which you will be in at the time of the meeting.">
                        <div class="col-md-3">
                            <label for="">Time Zone<span>*</span><br />
                                <small>Default time zone will be your Browser time zone. Please change it as per your
                                    meeting time zone. </small>
                            </label>

                            <select name="proposer_timezone" id="proposer_timezone" class="form-control mm365-single">
                                <?php echo apply_filters('mm365_meetings_list_timezones', 1) ?>
                            </select>


                        </div>
                    </div>
                    <div
                        data-intro="Please provide three time slots for meeting. The supplier can accept any one of them based on their convenience.">
                        <div class="form-row form-group">
                            <div class="col-sm-3">
                                <label for="">First preference <span>*</span></label>
                                <input type="text" name="first_choice" id="" class="meeting_date_1 form-control " required
                                    placeholder="" data-parsley-errors-container=".first_choice_error">
                                <div class="first_choice_error"></div>
                            </div>
                            <div class="col-6 col-sm-2">
                                <label for="">From<span>*</span></label>
                                <input type="text" name="first_choice_starttime" id="" class="from_time_1 form-control"
                                    required placeholder="" data-parsley-errors-container=".from_time_1_error">
                                <div class="from_time_1_error"></div>
                            </div>
                            <div class="col-6 col-sm-2">
                                <label for="">To<span>*</span></label>
                                <input type="text" name="first_choice_endtime" id="" class="to_time_1 form-control" required
                                    placeholder="" data-parsley-errors-container=".to_time_1_error">
                                <div class="to_time_1_error"></div>
                            </div>
                            <div class="col-4 d-flex align-items-end">
                                <span id="showdiff_1"></span>

                            </div>
                        </div>

                        <div class="form-row form-group">
                            <div class="col-sm-3">
                                <label for="">Second preference</label>
                                <input type="text" name="second_choice" id="" class="meeting_date_2 form-control"
                                    placeholder="">
                            </div>
                            <div class="col-6 col-sm-2">
                                <label for="">From</label>
                                <input type="text" name="second_choice_starttime" id="" class="from_time_2 form-control"
                                    placeholder="">
                            </div>
                            <div class="col-6 col-sm-2">
                                <label for="">To</label>
                                <input type="text" name="second_choice_endtime" id="" class="to_time_2 form-control"
                                    placeholder="">
                            </div>
                            <div class="col-4 d-flex align-items-end">
                                <span id="showdiff_2"></span>
                            </div>
                        </div>

                        <div class="form-row form-group">
                            <div class="col-sm-3">
                                <label for="">Third preference</label>
                                <input type="text" name="third_choice" id="" class="meeting_date_3 form-control"
                                    placeholder="">
                            </div>
                            <div class="col-6 col-sm-2">
                                <label for="">From</label>
                                <input type="text" name="third_choice_starttime" id="" class="from_time_3 form-control"
                                    placeholder="">
                            </div>
                            <div class="col-6 col-sm-2">
                                <label for="">To</label>
                                <input type="text" name="third_choice_endtime" id="" class="to_time_3 form-control"
                                    placeholder="">
                            </div>
                            <div class="col-4 d-flex align-items-end">
                                <span id="showdiff_3"></span>

                            </div>
                        </div>
                    </div>
                    <div class="form-row form-group" data-intro="Please enter the meeting agenda and notes if any.">
                        <div class="col-lg-12">
                            <label for="">
                                Agenda<span>*</span>
                                <br />
                                <small>Please enter the meeting agenda and notes if any.</small>
                            </label><br />
                            <textarea id="meeting_agenda" name="meeting_agenda" required
                                data-parsley-errors-container=".descError"></textarea>
                            <div class="descError"></div>
                        </div>
                    </div>

                </section>
                <!-- Button -->
                <div class="form-row pto-30">
                    <div class="col text-right">
                        <input type="hidden" id="after_schedule_redirect" name="after_schedule_redirect"
                            value="<?php echo esc_url(site_url() . "/meetings-scheduled"); ?>">
                        <button data-intro="Schedule the meeting details" type="submit"
                            class="btn btn-primary"><?php _e('Schedule Meeting', 'mm365') ?></button>
                    </div>
                </div>
            </form>

        <?php else:
            echo "Unauthorised action"; endif; ?>
    </div>
</div>
<?php
get_footer();
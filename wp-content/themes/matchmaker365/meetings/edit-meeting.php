<?php
/**
 * Template Name: Edit Meeting
 *
 */

$user = wp_get_current_user();
if(is_user_logged_in() AND in_array( 'business_user', (array) $user->roles )){

$mid        = $_REQUEST['mid'];
$nonce      = $_REQUEST['_wpnonce'];

if ( ! wp_verify_nonce( $nonce, 'edit_meeting' ) ) {
    die( __( 'Unauthorised token', 'mm365' ) ); 
}

$meeting_with_company   =  get_post_meta($mid, 'mm365_meeting_with_company');
$proposed_company_id    =  get_post_meta($mid, 'mm365_proposed_company_id', true);
$contact_person         =  get_post_meta($mid, 'mm365_meeting_with_contactperson', true );
$contact_email          =  get_post_meta($mid, 'mm365_meeting_with_contactemail', true );
$alternative_email      =  get_post_meta($mid, 'mm365_meeting_with_alt_contactemail', true );
$mr_id                  =  get_post_meta($mid, 'mm365_from_matchrequest', true );

$meeting_status         = get_post_meta($mid, 'mm365_meeting_status', true );
$owner                  = get_post_field( 'post_author', $mid );
$editing_user           = $user->ID; 

//Move back if already scheduled
if($meeting_status != 'proposed' OR ($owner != $editing_user )): wp_redirect(site_url()."/meetings-scheduled"); endif;
get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
  </div>
  <div class="dashboard-content-panel">
     <h1 class="heading-large pbo-10">
     <a href="#" onclick="history.back()"><img class="back-arrow" src="<?php echo get_template_directory_uri()?>/assets/images/arrow-left.svg" height="36px" alt=""></a>
     Edit meeting</h1>
    
            <!--New meeting form -->
        <form method="post" id="mm365_edit_meeting" action="#"  data-parsley-validate enctype="multipart/form-data" >
            <section class="company_preview">

                 <div class="form-row form-group" data-intro="Details of the supplier company">
                    <div class="col-md-5">
                        <label for="">Company Name</label><br/>
                        <?php echo esc_html($meeting_with_company[0]); ?>
                        <input type="hidden" name="meeting_with_company" value="<?php echo esc_html($meeting_with_company[0]); ?>">
                        <input type="hidden" name="meeting_with_company_id" value="<?php echo esc_html($meeting_with_company[1]); ?>">
                        <input type="hidden" name="proposed_company_id" id="requester_company_id" value="<?php echo $proposed_company_id; ?>" />
                        <input type="hidden" name="from_match_request" id="from_match_request" value="<?php echo esc_html($mr_id); ?>" />
                        <input type="hidden" name="exclude_post" id="exclude_post" value="<?php echo esc_html($mid); ?>" />
                    </div>
                    <div class="col-md-2">
                        <label for="">Contact Person</label><br/>
                        <?php echo esc_html($contact_person); ?>
                        <input type="hidden" name="meeting_contact_person" value="<?php echo esc_html($contact_person); ?>">
                    </div>
                    <div class="col-md-2 text-break">
                        <label for="">Email</label><br/>
                        <?php echo esc_html($contact_email); ?>
                        <input type="hidden" name="meeting_contact_email" value="<?php echo esc_html($contact_email); ?>">
                    </div>
                    <div class="col-md-2 text-break">
                        <label for="">Alternative Email</label><br/>
                        <?php echo ($alternative_email != NULL) ? $alternative_email:'-'; ?>
                    </div>
                </div>

                <div class="form-row form-group" data-intro="A short title which explains your requirement">
                    <div class="col-9">
                        <label for="">Title<span>*</span>
                        </label>
                        <input type="text" name="meeting_title" id="" class="form-control" required placeholder="Meeting title" value="<?php echo get_post_meta($mid, 'mm365_meeting_title', true ); ?>">
                    </div>
                    <div  class="col-lg-3" data-intro="">
                            <?php
                            $conf = get_post_meta( $mid, 'mm365_meeting_in_conference', true ); 
                            $upcomingConferences = apply_filters('mm365_offline_conferences_list', FALSE, TRUE);
                            ?>
                            <label for="">Conference Participation<span>*</span></label>
                            <select required name="conference_participation" id="associatedbuyer_upcoming_conference"
                                class="form-control">
                                <option value="">-Select-</option>
                                <option value="not-participating" <?php echo ($conf == 'not-participating') ? 'selected':''; ?>>Not Participating</option>
                                <optgroup label="Upcoming conferences">
                                <?php foreach ($upcomingConferences as $conference) {
                                    ?>
                                    <option <?php echo ($conf == $conference['ID']) ? 'selected':''; ?> value="<?php echo esc_html($conference['ID']); ?>">
                                        <?php echo esc_html($conference['name']); ?>
                                    </option>
                                    <?php
                                }
                                ?>
                                </optgroup>
                            </select>
                        </div>
                </div>

                <div class="form-row form-group" data-intro="Propose preferred three dates and times when you would like to schedule the meeting. It is required to enter the First Preference.">
                    <div class="col">
                        <label for="">Meeting date & time<br/>
                        <small>Propose preferred three dates and times when you would like to schedule the meeting. It is required to enter the First Preference.</small>
                        </label>
                    </div>
                </div>

                <div class="form-row form-group" data-intro="Your timezone. If you are travelling between timezones please do choose the timezone which you will be in at the time of the meeting.">
                    <div class="col-md-3">
                       <label for="">Time Zone<span>*</span><br/>
                       <small>Default time zone will be your Browser time zone (<?php echo $_COOKIE['user_timezone']; ?>). Please change it  as per your meeting time zone. </small></label>
                       <select name="proposer_timezone" id="proposer_timezone" class="form-control mm365-single">

                       <?php  
                       $existin_tz = get_post_meta($mid, 'mm365_proposer_timezone', true ); 
                       ($existin_tz == '') ? $tz = $_COOKIE['user_timezone'] : $tz = $existin_tz; 
                       ?>
                      
                        <?php echo apply_filters('mm365_meetings_list_timezones',$tz)  ?>
                       </select>
                    </div>
                </div>
          <div  data-intro="Please provide three time slots for meeting. The supplier can accept any one of them based on their convenience.">
                <div class="form-row form-group">
                    <div class="col-md-3">
                        <label for="">First preference <span>*</span></label>
                        <input type="text" name="first_choice" id="" class="meeting_date_1 form-control " required placeholder="" value="<?php  echo get_post_meta($mid, 'mm365_meeting_date_1', true ); ?>"  data-parsley-errors-container=".first_choice_error">
                        <div class="first_choice_error"></div>  
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="">From<span>*</span></label>
                        <input type="text" name="first_choice_starttime" id="" class="from_time_1 form-control" required placeholder="" value="<?php  echo get_post_meta($mid, 'mm365_meeting_date_1_from', true ); ?>" data-parsley-errors-container=".from_time_1_error">
                        <div class="from_time_1_error"></div>  
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="">To<span>*</span></label>
                        <input type="text" name="first_choice_endtime" id="" class="to_time_1 form-control" required placeholder="" value="<?php  echo get_post_meta($mid, 'mm365_meeting_date_1_to', true ); ?>" data-parsley-errors-container=".to_time_1_error">
                        <div class="to_time_1_error"></div>  
                    </div>
                    <div class="col-4 d-flex align-items-end">
                       <span id="showdiff_1">
                       <?php $time_diff = apply_filters('mm365_meetings_time_to_timestamp',get_post_meta($mid, 'mm365_meeting_date_1', true ),get_post_meta($mid, 'mm365_meeting_date_1_from', true ),get_post_meta($mid, 'mm365_meeting_date_1_to', true ),"difference"); 
                              if($time_diff['hours'] > 0 OR $time_diff['minutes'] > 0){
                               echo "Duration: "; if($time_diff['hours'] > 0) { $time_diff['hours']."h "; } echo $time_diff['minutes']."m";
                              }
                       ?>
                       </span>
                    </div>
                </div>

                <div class="form-row form-group">
                    <div class="col-md-3">
                    <label for="">Second preference</label>
                        <input type="text" name="second_choice" id="" class="meeting_date_2 form-control"  value="<?php  echo get_post_meta($mid, 'mm365_meeting_date_2', true ); ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="">From</label>
                        <input type="text" name="second_choice_starttime" id="" class="from_time_2 form-control"  value="<?php  echo get_post_meta($mid, 'mm365_meeting_date_2_from', true ); ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="">To</label>
                        <input type="text" name="second_choice_endtime" id="" class="to_time_2 form-control"  value="<?php  echo get_post_meta($mid, 'mm365_meeting_date_2_to', true ); ?>">
                    </div>
                    <div class="col-4 d-flex align-items-end">
                       <span id="showdiff_2">
                       <?php $time_diff = apply_filters('mm365_meetings_time_to_timestamp',get_post_meta($mid, 'mm365_meeting_date_2', true ),get_post_meta($mid, 'mm365_meeting_date_2_from', true ),get_post_meta($mid, 'mm365_meeting_date_2_to', true ),"difference"); 
                              if($time_diff['hours'] > 0 OR $time_diff['minutes'] > 0){
                                echo "Duration: "; if($time_diff['hours'] > 0) { $time_diff['hours']."h "; } echo $time_diff['minutes']."m";
                              }
                       ?>                       
                       </span>
                    </div>
                </div>

                <div class="form-row form-group">
                    <div class="col-md-3">
                    <label for="">Third preference</label>
                        <input type="text" name="third_choice" id="" class="meeting_date_3 form-control"  value="<?php  echo get_post_meta($mid, 'mm365_meeting_date_3', true ); ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="">From</label>
                        <input type="text" name="third_choice_starttime" id="" class="from_time_3 form-control"  value="<?php  echo get_post_meta($mid, 'mm365_meeting_date_3_from', true ); ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="">To</label>
                        <input type="text" name="third_choice_endtime" id="" class="to_time_3 form-control"  value="<?php  echo get_post_meta($mid, 'mm365_meeting_date_3_to', true ); ?>">
                    </div>
                    <div class="col-4 d-flex align-items-end">
                       <span id="showdiff_3">
                       <?php $time_diff = apply_filters('mm365_meetings_time_to_timestamp',get_post_meta($mid, 'mm365_meeting_date_3', true ),get_post_meta($mid, 'mm365_meeting_date_3_from', true ),get_post_meta($mid, 'mm365_meeting_date_3_to', true ),"difference"); 
                             if($time_diff['hours'] > 0 OR $time_diff['minutes'] > 0){
                                 echo "Duration: "; if($time_diff['hours'] > 0) { $time_diff['hours']."h "; } echo $time_diff['minutes']."m";
                             }
                       ?>
                       </span>
                    </div>
                </div>
         </div>
                <div class="form-row form-group" data-intro="Please enter the meeting agenda and notes if any.">
                <div class="col-lg-12">
                        <label for="">
                          Agenda<span>*</span><br/>
                          <small>Please enter the meeting agenda and notes if any.</small>
                        </label><br/>
                        <textarea id="meeting_agenda" name="meeting_agenda" required data-parsley-errors-container=".descError">
                            <?php echo get_post_meta($mid, 'mm365_meeting_agenda', true ); ?>
                        </textarea>
                        <div class="descError"></div> 
                </div>
                </div>

                </section>
                <!-- Button -->
                <div class="form-row pto-30">
                    <div class="col text-right">
                        <input type="hidden" id="after_schedule_redirect" name="after_schedule_redirect" value="<?php echo esc_url(site_url()."/meetings-scheduled");?>">
                        <input type="hidden" id="mid" name="mid" value="<?php echo esc_html($mid);?>">
                        <button  data-intro="Update the meeting details" type="submit" class="btn btn-primary" ><?php _e('Update Meeting', 'mm365') ?></button>
                    </div>
                </div>
        </form>

  </div>
</div>
<?php } else {       
      $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      wp_redirect(wp_login_url($actual_link));?>
<h2>Please sign in to continue</h2>
<?php } ?>
<?php
get_footer();
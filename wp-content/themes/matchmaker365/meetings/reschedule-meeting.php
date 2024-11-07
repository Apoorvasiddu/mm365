<?php
/**
 * Template Name: Reschedule Meeting
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['business_user']);

// //Check if user has active registration else redirect
do_action('mm365_helper_check_companyregistration', 'register-your-company');

$company_id = $_COOKIE['active_company_id'];

$mid        = $_REQUEST['mid'];
$nonce      = $_REQUEST['_wpnonce'];

if ( ! wp_verify_nonce( $nonce, 'reschedule_meeting' ) ) {
    die( __( 'Unauthorised token', 'mm365' ) ); 
}

$meeting_mode           = apply_filters('mm365_meetings_ownership_check',$mid,$company_id);
$meeting_status         = get_post_meta($mid, 'mm365_meeting_status', true );
$owner                  = get_post_field( 'post_author', $mid );
$editing_user           = $user->ID; 


if($meeting_mode == 'invited'){
    $info           = get_post_meta( $mid, 'mm365_proposed_company');
    $with_company   = $info[0];
    $contact_person = $info[1];
    $contact_email          = $info[2];
}elseif($meeting_mode == 'scheduled'){
    $info           = get_post_meta( $mid, 'mm365_meeting_with_company');
    $with_company   = $info[0];
    $contact_person = get_post_meta( $mid, 'mm365_meeting_with_contactperson', true );
    $contact_email  = get_post_meta( $mid, 'mm365_meeting_with_contactemail', true );
}


if(isset($_COOKIE['user_timezone'])):
    $viewer_timezone =  $_COOKIE['user_timezone'];
else:
    $viewer_timezone = 'UTC';
endif;

//Move back if already scheduled
if(!in_array($meeting_status, array('scheduled','rescheduled'))): wp_redirect(site_url()."/meetings-scheduled"); endif;
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
     Reschedule meeting</h1>
    
            <!--New meeting form -->
        
            <section class="company_preview">

                 <div class="form-row form-group">
                    <div class="col-md-5">
                        <label for="">Company Name</label><br/>
                        <?php echo esc_html($with_company); ?>
                    </div>
                    <div class="col-md-2">
                        <label for="">Contact Person</label><br/>
                        <?php echo esc_html($contact_person); ?>
                    </div>
                    <div class="col-md-2 text-break">
                        <label for="">Email</label><br/>
                        <?php echo esc_html($contact_email); ?>
                    </div>
                </div>

                <div class="form-row form-group">
                    <div class="col">
                        <label for="">Title
                        </label><br/>
                        <?php echo get_post_meta($mid, 'mm365_meeting_title', true ); ?>
                    </div>
                </div>


                <div class="form-row form-group">
                <div class="col-lg-12">
                        <label for="">
                          Agenda
                        </label><br/>
                        <?php echo get_post_meta($mid, 'mm365_meeting_agenda', true ); ?>
                </div>
                </div>

                <div class="form-row">
                    <div class="col-md-3">
                    <label for="">Current meeting time</label>
                        <div class="time-slot active">
                        
                        <p><?php 
                            $accepted_slot = get_post_meta( $mid, 'mm365_accepted_meeting_slot', true );
                            if($accepted_slot != 4):
                                //Adjust array position 
                                $array_pos      = ($accepted_slot - 1);
                                $slot           = get_post_meta( $mid, 'mm365_meeting_slots');
                                $accepted_date  = explode("|",$slot[$array_pos]);            
                                //TimeZone conversion
                                $proposer_timezone = get_post_meta( $mid, 'mm365_proposer_timezone', true );
                                ($proposer_timezone != '') ? $proposer_timezone = $proposer_timezone : $proposer_timezone = 'UTC';

                                $date              =  apply_filters('mm365_meetings_convert_time', $accepted_date[0], $proposer_timezone, $viewer_timezone , 'm/d/Y' );
                                $from              =  apply_filters('mm365_meetings_convert_time', $accepted_date[0], $proposer_timezone, $viewer_timezone , 'h:i A' );
                                $to                =  apply_filters('mm365_meetings_convert_time', $accepted_date[1], $proposer_timezone, $viewer_timezone , 'h:i A' );
            
                                echo '<i class="far fa-calendar" aria-hidden="true"></i>'.$date."<br/><i class='far fa-clock' aria-hidden='true'></i>".$from." - ".$to." <br/><small>Converted to ".$viewer_timezone." timezone</small><br/>"; 
                                $time_diff = apply_filters('mm365_meetings_time_to_timestamp',$date,$from,$to,'difference'); 
                                echo "<i class='far fa-hourglass' aria-hidden='true'></i>".$time_diff['hours']."h ".$time_diff['minutes']."m";
            
                            else:
                                //Time zone conversion pending
                                $rescheduled_requested_to = get_post_meta( $mid, 'mm365_meeting_reschedule_timestamp', true );
                                $proposed_date = explode("|",$rescheduled_requested_to);
            
                                $attendee_timezone = get_post_meta( $mid, 'mm365_attendee_timezone', true );
                                ($attendee_timezone != '') ? $attendee_timezone = $attendee_timezone : $attendee_timezone = 'UTC';
                                
                                $date              =  apply_filters('mm365_meetings_convert_time', $proposed_date[0], $attendee_timezone, $viewer_timezone , 'm/d/Y' );
                                $from              =  apply_filters('mm365_meetings_convert_time', $proposed_date[0], $attendee_timezone, $viewer_timezone , 'h:i A' );
                                $to                =  apply_filters('mm365_meetings_convert_time', $proposed_date[1], $attendee_timezone, $viewer_timezone , 'h:i A' );
                                echo '<i class="far fa-calendar" aria-hidden="true"></i>'.$date."<br/><i class='far fa-clock' aria-hidden='true'></i>".$from." - ".$to." <br/><small>Converted to ".$viewer_timezone." timezone</small><br/>"; 
                                $time_diff = apply_filters('mm365_meetings_time_to_timestamp',$date,$from,$to,'difference'); 
                                echo "<i class='far fa-hourglass' aria-hidden='true'></i>".$time_diff['hours']."h ".$time_diff['minutes']."m";
                            endif;
            
                         ?></p>
                        </div>
                        
                    </div>
                </div>
                <?php 
                       $meeting_with_company   =  get_post_meta($mid, 'mm365_meeting_with_company');
                    ?>
                    <form method="post" id="mm365_reschedule_meeting" action="#"  data-parsley-validate enctype="multipart/form-data" >
                        <input type="hidden" name="meeting_with_company" value="<?php echo esc_html($meeting_with_company[0]); ?>">
                        <input type="hidden" name="meeting_with_company_id" value="<?php echo esc_html($meeting_with_company[1]); ?>">
                        <input type="hidden" name="proposed_company_id" id="requester_company_id" value="<?php echo get_post_meta( $mid, 'mm365_proposed_company_id', true); ?>" />
                <div class="form-row">
                    <div class="col-12">
                    <br/>
                     <label for="">New meeting date & time </label>
                    </div>
                </div>
                <div class="form-row form-group">
                    <div class="col-md-3">
                       <label for="">Time Zone<span>*</span></label>
                       <select name="rescheduler_timezone" id="rescheduler_timezone" class="form-control mm365-single">
                       <?php  echo apply_filters('mm365_meetings_list_timezones',$viewer_timezone);  ?>
                       </select>
                    </div>
                </div>
                <div class="form-row form-group">
                    <div class="col-md-3">
                        <label for="">Date <span>*</span></label>
                        <input type="text" name="first_choice" id="" class="meeting_date_1 form-control " required placeholder="" data-parsley-errors-container=".first_choice_error">
                        <div class="first_choice_error"></div>  
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="">From<span>*</span></label>
                        <input type="text" name="first_choice_starttime" id="" class="from_time_1 form-control" required placeholder="" data-parsley-errors-container=".from_time_1_error">
                        <div class="from_time_1_error"></div>  
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="">To<span>*</span></label>
                        <input type="text" name="first_choice_endtime" id="" class="to_time_1 form-control" required placeholder=""  data-parsley-errors-container=".to_time_1_error" >
                        <div class="to_time_1_error"></div>  
                    </div>
                    <div class="col-4 d-flex align-items-end">
                       <span id="showdiff_1"></span>
                    </div>
                </div>
                <div class="form-row form-group">
                    <div class="col-12">
                        <label for="">Reason for rescheduling<span>*</span><br/>
                        <small>Please enter the reason for rescheduling the meeting</small></label><br/>
                        <textarea class="form-control" required name="reason_for_active_reschedule" id="reason_for_active_reschedule"  rows="2"></textarea>
                    </div>
                </div>
                                <!-- Button -->
                <div class="form-row pto-30">
                    <div class="col text-right">
                       
                        <?php if($meeting_mode == 'invited'): ?>
                        <input type="hidden" id="after_schedule_redirect" name="after_schedule_redirect" value="<?php echo esc_url(site_url()."/meeting-invites");?>">
                        <input type="hidden" id="rescheduled_by" name="rescheduled_by" value="attendee">
                        <?php else: ?>
                        <input type="hidden" id="after_schedule_redirect" name="after_schedule_redirect" value="<?php echo esc_url(site_url()."/meetings-scheduled");?>">
                        <input type="hidden" id="rescheduled_by" name="rescheduled_by" value="proposer">
                        <?php endif; ?>
                        <input type="hidden" id="mid" name="mid" value="<?php echo esc_html($mid);?>">

                        <!-- <input type="hidden" id="rescheduler_timezone" name="rescheduler_timezone" value="<?php echo $viewer_timezone;?>"> -->
                        <button type="submit" class="btn btn-primary" ><?php _e('Reschedule Meeting', 'mm365') ?></button>
                    </div>
                </div>
                </form>
           

                </section>

      

  </div>
</div>

<?php
get_footer();
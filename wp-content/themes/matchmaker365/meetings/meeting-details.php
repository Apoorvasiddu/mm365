<?php
/**
 * Template Name: Meeting Details
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['business_user']);

// //Check if user has active registration else redirect
do_action('mm365_helper_check_companyregistration', 'register-your-company');
$company_id = $_COOKIE['active_company_id'];

$mid            = $_REQUEST['mid'];
//$company_id = $_REQUEST['cid'];
//$nonce          = $_REQUEST['_wpnonce'];

$wp_post_status = get_post_status($mid);
$meeting_mode   = apply_filters('mm365_meetings_ownership_check',$mid,$company_id);

if (($meeting_mode == 'unauth' OR $wp_post_status != 'publish')) {
    die( __( 'Unauthorised access', 'mm365' ) ); 
}



//General
$meeting_status = get_post_meta( $mid, 'mm365_meeting_status',true);
//For scheduled
if($meeting_mode == 'invited'){
    $info           = get_post_meta( $mid, 'mm365_proposed_company');
    $with_company   = $info[0];
    $contact_person = $info[1];
    $email          = $info[2];
    $alt_email          = ''; //intentionally Left blank 
}elseif($meeting_mode == 'scheduled'){
    $info           = get_post_meta( $mid, 'mm365_meeting_with_company');
    $with_company   = $info[0];
    $contact_person = get_post_meta( $mid, 'mm365_meeting_with_contactperson', true );
    $email          = get_post_meta( $mid, 'mm365_meeting_with_contactemail', true );
    $alt_email      = get_post_meta( $mid, 'mm365_meeting_with_alt_contactemail', true );
}
$attendee_timezone = get_post_meta( $mid, 'mm365_attendee_timezone', true );
$proposer_timezone = get_post_meta( $mid, 'mm365_proposer_timezone', true );
($proposer_timezone != '') ? $proposer_timezone = $proposer_timezone : $proposer_timezone = 'UTC';
($attendee_timezone != '') ? $attendee_timezone = $attendee_timezone : $attendee_timezone = 'UTC';
//Invite
get_header();


if(isset($_COOKIE['user_timezone'])):
    $viewer_timezone =  $_COOKIE['user_timezone'];
else:
    $viewer_timezone = 'UTC';
endif;
  

?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
  </div>
  <div class="dashboard-content-panel">
  
     <h1 class="heading-large pbo-10">
     <a href="#" onclick="history.back()"><img class="back-arrow" src="<?php echo get_template_directory_uri()?>/assets/images/arrow-left.svg" height="36px" alt="">
     Meeting <?php echo " - ".$with_company; ?></a> - <?php echo "<span class='meeting_status ".$meeting_status."' data-intro='Current meeting status'>".preg_replace('/\_+/', ' ', $meeting_status)."</span>"; ?></h1>
     <section class="company_preview">
     
     <!-- status block -->
     <div class="row">
        <div class="col-md-6" data-intro="Meeting stages. Both parties can see the current status of the meeting">
            <ul class="meeting-status">
                <li><div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/success.svg" alt=""></div></li>

                <?php if(in_array($meeting_status,array('accepted','proposed_new_time','scheduled','rescheduled','meeting_declined','cancelled'))): ?>
                    <li><div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/success.svg" alt=""></div></li>
                <?php elseif(in_array($meeting_status,array('declined'))): ?>
                   <li class="error"><div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/failure.svg" alt=""></div></li>
                <?php else: ?>    
                    <li class="inactive"><div>2</div></li>
                <?php endif; ?>    
                <!-- <li class="inactive"><div>2</div></li> -->
                <!--  -->
                <?php if(in_array($meeting_status,array('scheduled','rescheduled'))): ?>
                    <li><div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/success.svg" alt=""></div></li>
                <?php elseif(in_array($meeting_status,array('meeting_declined','cancelled'))): ?>
                   <li class="error"><div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/failure.svg" alt=""></div></li>
                <?php else: ?>  
                    <li class="inactive"><div>3</div></li>
                <?php endif; ?>  
            </ul>  
            <ul class="meeting-guidetext">
                <li>Request sent</li>
                <li>Request accepted</li>
                <li>Meeting scheduled</li>
            </ul> 
        </div>
     </div>
     <hr/>
     <!-- status block end here -->

     <!-- Details block -->
     <div class="row">
        <div class="col-md-8" data-intro="Meeting title">
            <label for="">Meeting title</label><br/>
            <?php echo get_post_meta( $mid, 'mm365_meeting_title', true ); ?><br/><br/>
        </div>
        <div class="col-md-4" data-intro="Part of conference">
            <label for="">Part of conference</label><br/>
            <?php 
            $conf = get_post_meta( $mid, 'mm365_meeting_in_conference', true ); 
            if($conf == 'not-participating' OR $conf == null){
                echo "No";
            }else{
                echo '<a class="conference_link" href="'.site_url('view-offline-conference').'/?conf_id='.$conf.'" target="_blank">'.get_the_title($conf).'&nbsp;
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
</svg>

                </a>';
            }
            ?>
        </div>
     </div>

     <div class="row" data-intro="Detail of company and concerned person whom you are meeting">
         <div class="col-md-3">
             <label for="">Meeting with company</label><br/>
             <p><?php  echo $with_company; ?></p>
         </div>
         <div class="col-md-3">
             <label for="">Contact person</label><br/>
             <p><?php echo $contact_person; ?></p>
         </div>
         <div class="col-md-3">
             <label for="">Email</label><br/>
             <p><?php echo $email; ?></p>
         </div>
         <div class="col-md-3">
             <label for="">Alternative Email</label><br/>
             <p><?php echo ($alt_email != NULL)? $alt_email : '-'; ?></p>
         </div>
     </div>

     <div class="row">
         <div class="col-md-12" data-intro="Meeting agenda">
             <label for="">Agenda</label><br/>
             <?php echo get_post_meta( $mid, 'mm365_meeting_agenda', true ); ?>
         </div>
     </div>

   <!-- loop slots ends-->

   <!-- details block ends here-->

   <!-- forms block -->
   <?php if($meeting_status == 'proposed' || $meeting_status == 'declined' || $meeting_status == 'cancelled' || $meeting_status == 'meeting_declined'): ?>
     <div class="row">
         <div class="col-md-12">
             <label for="">Meeting date & time<br/>
             <?php if($meeting_mode == 'invited'  AND $meeting_status == 'proposed'): ?>
               <small>Buyer has proposed three meeting time slots. You can select any one proposed time slot and accept the meeting request or request a new time slot for the meeting.</small>
             <?php endif; ?>
             </label>
            </div>
     </div>

     <section class="time-slot-group  <?php if($meeting_mode == 'invited'  AND $meeting_status == 'proposed'): ?>invited_mode<?php endif; ?>">
     <form method="post" id="mm365_meeting_invite" action="#"  data-parsley-validate enctype="multipart/form-data" >
     <div class="row" data-intro="Time slots proposed by the buyer. Supplier can accept any one of the slots, once accepted buyer can finish setting up the meeting">
        
         <div class="col-md-3">
            <?php
               $proposer_timezone = get_post_meta( $mid, 'mm365_proposer_timezone', true );
    
               if(!$proposer_timezone){ $proposer_timezone = 'UTC'; }
               
               $slots = get_post_meta( $mid, 'mm365_meeting_slots');

               $check_time = time();
               $slot_1 = explode("|",$slots[0]);
               $s1_start = $slot_1[0];
               $s1_end = $slot_1[1];
               //Check if time is gone past
               $s1_over = ($s1_start < $check_time) ? ' - expired' : '';

            ?>
            <div class="time-slot <?php if($meeting_mode == 'invited' AND $meeting_status == 'proposed' AND $s1_over ==''): ?>active <?php endif;  echo $s1_over ?> " <?php if($s1_over == ''): ?> data-value="first_slot" <?php endif; ?> >
             <label for="">First preference <?php echo $s1_over; ?></label>
             <p><?php 

                $meeting_slot   =  apply_filters('mm365_meetings_time_to_timestamp', date('m/d/Y',$s1_start),date('h:ia',$s1_start),date('h:ia',$s1_end));
                $date           =  apply_filters('mm365_meetings_convert_time', $meeting_slot['start'], $proposer_timezone, $viewer_timezone , 'm/d/Y' );
                $from           =  apply_filters('mm365_meetings_convert_time', $meeting_slot['start'], $proposer_timezone, $viewer_timezone , 'h:i A' );
                $to             =  apply_filters('mm365_meetings_convert_time', $meeting_slot['end'],   $proposer_timezone, $viewer_timezone , 'h:i A' );

                echo '<i class="far fa-calendar" aria-hidden="true"></i>'.$date."<br/><i class='far fa-clock' aria-hidden='true'></i>".$from." - ".$to." <br/><small>Converted to ".$viewer_timezone." timezone</small><br/>"; 
                $time_diff = apply_filters('mm365_meetings_time_to_timestamp',date('m/d/Y',$s1_start),date('h:ia',$s1_start),date('h:ia',$s1_end),'difference'); 
                echo "<i class='far fa-hourglass' aria-hidden='true'></i>".$time_diff['hours']."h ".$time_diff['minutes']."m";

                
             ?></p>
            </div>

         </div>
         <?php 

             if( get_post_meta( $mid, 'mm365_meeting_date_2', true ) != '' AND get_post_meta( $mid, 'mm365_meeting_date_2_to', true ) != ''):
                $slot_2 = explode("|",$slots[1]);
                $s2_start = $slot_2[0];
                $s2_end = $slot_2[1];
                //Check if time is gone past
                $s2_over = ($s2_start < $check_time) ? ' - expired' : '';
         ?>
         <div class="col-md-3">
             <div class="time-slot <?php echo $s2_over; ?>" <?php if($s2_over == ''): ?> data-value="second_slot" <?php endif; ?>>
             <label for="">Second preference <?php echo $s2_over; ?></label>
             <p><?php 
                
                $meeting_slot   =  apply_filters('mm365_meetings_time_to_timestamp',date('m/d/Y',$s2_start),date('h:ia',$s2_start),date('h:ia',$s2_end));
                $date           =  apply_filters('mm365_meetings_convert_time',$meeting_slot['start'], $proposer_timezone, $viewer_timezone , 'm/d/Y' );
                $from           =  apply_filters('mm365_meetings_convert_time',$meeting_slot['start'], $proposer_timezone, $viewer_timezone , 'h:i A' );
                $to             =  apply_filters('mm365_meetings_convert_time',$meeting_slot['end'],   $proposer_timezone, $viewer_timezone , 'h:i A' );

                echo '<i class="far fa-calendar" aria-hidden="true"></i>'.$date."<br/><i class='far fa-clock' aria-hidden='true'></i>".$from." - ".$to." <br/><small>Converted to ".$viewer_timezone." timezone</small><br/>"; 
                $time_diff = apply_filters('mm365_meetings_time_to_timestamp',date('m/d/Y',$s2_start),date('h:ia',$s2_start),date('h:ia',$s2_end),'difference'); 
                echo "<i class='far fa-hourglass' aria-hidden='true'></i>".$time_diff['hours']."h ".$time_diff['minutes']."m";

             ?></p>
             </div>
         </div><?php endif; ?>
         <?php 

             if(  get_post_meta( $mid, 'mm365_meeting_date_3', true ) != ''  AND get_post_meta( $mid, 'mm365_meeting_date_3_to', true ) != ''):
                $slot_3 = explode("|",$slots[2]);
                $s3_start = $slot_3[0];
                $s3_end = $slot_3[1];
                //Check if time is gone past
                $s3_over = ($s3_start < $check_time) ? ' - expired' : '';
         ?>
         <div class="col-md-3">
             <div class="time-slot <?php echo $s3_over; ?>" <?php if($s3_over == ''): ?> data-value="third_slot" <?php endif; ?> >
             <label for="">Third preference <?php echo $s3_over; ?></label>
             <p><?php 

                $meeting_slot   =  apply_filters('mm365_meetings_time_to_timestamp',date('m/d/Y',$s3_start),date('h:ia',$s3_start),date('h:ia',$s3_end));
                $date           =  apply_filters('mm365_meetings_convert_time', $meeting_slot['start'], $proposer_timezone, $viewer_timezone , 'm/d/Y' );
                $from           =  apply_filters('mm365_meetings_convert_time', $meeting_slot['start'], $proposer_timezone, $viewer_timezone , 'h:i A' );
                $to             =  apply_filters('mm365_meetings_convert_time', $meeting_slot['end'],   $proposer_timezone, $viewer_timezone , 'h:i A' );

                echo '<i class="far fa-calendar" aria-hidden="true"></i>'.$date."<br/><i class='far fa-clock' aria-hidden='true'></i>".$from." - ".$to." <br/><small>Converted to ".$viewer_timezone." timezone</small><br/>"; 
                $time_diff = apply_filters('mm365_meetings_time_to_timestamp',date('m/d/Y',$s3_start),date('h:ia',$s3_start),date('h:ia',$s3_end),'difference'); 
                echo "<i class='far fa-hourglass' aria-hidden='true'></i>".$time_diff['hours']."h ".$time_diff['minutes']."m";

             ?></p>
             </div>
         </div><?php endif; ?>
         <div class="col-md-3">

         </div>
     </div>
     <?php if($meeting_mode == 'invited' AND $meeting_status == 'proposed'): ?>
     <div class="row pto-30">
         <div class="col-md-9" data-intro="If the proposed timeslots are not acceptable, supplier can suggest a new time slot for meeting">
            <div class="time-slot" data-value="requesting_new_slot">
                <label for="">Propose new time</label>
                <div class="form-row form-group">
                    <div class="col-md-3">
                       <label for="">Time Zone<span class="cnd_1">*</span></label>
                       <select name="attendee_timezone" id="attendee_timezone" class="form-control mm365-single">
                       <?php  echo apply_filters('mm365_meetings_list_timezones',$viewer_timezone);  ?>
                       </select>
                    </div>
                </div>
                <div class="form-row form-group">
                    <div class="col-4">
                        Preffered date<span class="cnd_1 text-danger">*</span><br/>
                        <input type="text" name="first_choice" id="" class="meeting_date_1 form-control "  placeholder=""  data-parsley-errors-container=".first_choice_error">
                        <div class="first_choice_error"></div>  
                    </div>
                    <div class="col-2">
                      From<span class="cnd_1 text-danger">*</span><br/>
                      <input type="text" name="first_choice_starttime" id="" class="from_time_1 form-control"  placeholder=""  data-parsley-errors-container=".from_time_1_error">
                      <div class="from_time_1_error"></div>  
                    </div>
                    <div class="col-2">
                        To<span class="cnd_1 text-danger">*</span><br/>
                        <input type="text" name="first_choice_endtime" id="" class="to_time_1 form-control"  placeholder="" data-parsley-errors-container=".to_time_1_error">
                        <div class="to_time_1_error"></div>  
                    </div>
                    <div class="col-4 d-flex align-items-end">
                       <span id="showdiff_1"></span>
                    </div>
                    <input type="hidden" name="meeting_with_company_id" value="<?php echo $company_id; ?>">
                    <input type="hidden" name="proposed_company_id" id="requester_company_id" value="<?php echo get_post_meta( $mid, 'mm365_proposed_company_id', true); ?>" />
                </div>
            </div>    
         </div>
     </div>    
     <div class="row pto-30">
         <div class="col-md-9" data-intro="If you are not inerested in the meeting. You can decline the invite along with the reason for declining.">
            <div class="time-slot" data-value="decline_invite">
                <label for="">Decline invite<span class="cnd_2">*</span></label>
                <div class="form-row form-group">
                    <div class="col-12">
                        <small>Please enter the reason for declining the meeting invite</small>
                        <textarea name="decline_invite" id="meeting_decline_reason" class="form-control" id=""  rows="2"></textarea>
                    </div>
                    
                </div>
            </div>    
         </div>
     </div>  

     <?php if($s1_over == ''): ?>
        <input type="hidden" id="radio-value" name="radio-value" value="first_slot"/>
    <?php else: ?>
        <input type="hidden" id="radio-value" name="radio-value" value=""/>
    <?php endif; ?>

     <input type="hidden" name="meeting_id" id="meeting_id" value="<?php echo esc_html($mid); ?>"/>
     <div class="form-row pto-30">
        <div class="col text-right d-flex flex-column flex-md-row-reverse">
                <button type="submit" class="btn btn-primary" ><?php _e('Submit', 'mm365') ?></button>
        </div>
    </div>
    <?php endif; ?>
    </form>
    </section>  
  <?php endif; ?>

<!-- forms block ends here -->

<!-- Status based messages -->

<?php if($meeting_status == 'accepted' || $meeting_status == 'cancelled' || $meeting_status == 'meeting_declined'): 
    $accepted_slot = get_post_meta( $mid, 'mm365_accepted_meeting_slot', true );
    if($accepted_slot != '' AND $accepted_slot != '4'):
    ?>
    <div class="row pto-20">
         <div class="col-md-3" data-intro="Accpeted time slot for meeting">
             <label for="">Accepted timeslot</label><br/>
             <div class="time-slot active">
             <p><?php 
                
                //Adjust array position 
                $array_pos      = ($accepted_slot - 1);
                $slot           = get_post_meta( $mid, 'mm365_meeting_slots');
                $accepted_date  = explode("|",$slot[$array_pos]);
                //$proposer_timezone = get_post_meta( $mid, 'mm365_proposer_timezone', true );
                //timezone conversion
                $date              =  apply_filters('mm365_meetings_convert_time',  $accepted_date[0], $proposer_timezone, $viewer_timezone , 'm/d/Y' );
                $from              =  apply_filters('mm365_meetings_convert_time',  $accepted_date[0], $proposer_timezone, $viewer_timezone , 'h:i A' );
                $to                =  apply_filters('mm365_meetings_convert_time',  $accepted_date[1], $proposer_timezone, $viewer_timezone , 'h:i A' );

                echo '<i class="far fa-calendar" aria-hidden="true"></i>'.$date."<br/><i class='far fa-clock' aria-hidden='true'></i>".$from." - ".$to." <br/><small>Converted to ".$viewer_timezone." timezone</small><br/>"; 
                $time_diff = apply_filters('mm365_meetings_time_to_timestamp', $date,$from,$to,'difference'); 
                echo "<i class='far fa-hourglass' aria-hidden='true'></i>".$time_diff['hours']."h ".$time_diff['minutes']."m";

             ?></p>
             </div>
         </div>
     </div>
  <?php endif; endif; ?>


<?php if($meeting_status == 'proposed_new_time' || $meeting_status == 'cancelled' || $meeting_status == 'meeting_declined'): 
      $rescheduled_requested_to = get_post_meta( $mid, 'mm365_meeting_reschedule_timestamp', true );
      if($rescheduled_requested_to != ''):
    ?>
    <div class="row pto-20">
         <div class="col-md-3" data-intro="Since the meeting slots you send is not acceptable for the supplier, they have suggested a new time slot. If this slot is acceptable for you, you can setup the meeting. If not, please cancel this meeting and create new meeting invite ">
             <label for="">Proposed new timeslot</label><br/>
             <div class="time-slot proposed">
             <p><?php 
                $proposed_date     = explode("|",$rescheduled_requested_to);

                $date              =  apply_filters('mm365_meetings_convert_time',  $proposed_date[0], $attendee_timezone, $viewer_timezone , 'm/d/Y' );
                $from              =  apply_filters('mm365_meetings_convert_time',  $proposed_date[0], $attendee_timezone, $viewer_timezone , 'h:i A' );
                $to                =  apply_filters('mm365_meetings_convert_time',  $proposed_date[1], $attendee_timezone, $viewer_timezone , 'h:i A' );
                echo '<i class="far fa-calendar" aria-hidden="true"></i>'.$date."<br/><i class='far fa-clock' aria-hidden='true'></i>".$from." - ".$to." <br/><small>Converted to ".$viewer_timezone." timezone</small><br/>"; 
                $time_diff = apply_filters('mm365_meetings_time_to_timestamp',$date,$from,$to,'difference'); 
                echo "<i class='far fa-hourglass' aria-hidden='true'></i>".$time_diff['hours']."h ".$time_diff['minutes']."m";

             ?></p>
             </div>
         </div>
         <div class="col-md-3">
           <label for="">Supplier Timezone</label>
           <br/><?php echo $attendee_timezone; ?>
         </div>
     </div>
  <?php endif; endif; ?>



  <?php if(in_array($meeting_status,array('scheduled','rescheduled','meeting_declined','cancelled','declined'))): 
    $meeting_type    = get_post_meta( $mid, 'mm365_meeting_type', true ); 
    $meeting_details = get_post_meta( $mid, 'mm365_meeting_details', true );

    if($meeting_type != '' AND $meeting_type != ''):
    ?>
    <div class="row pto-20">
         <div class="col-md-3" data-intro="How the meeting is happeing. It can be any of the popular virtual meeting platforms or can be direct meeting.">
             <label for="">Meeting type</label><br/>
             <?php              
             $meeting_types = apply_filters('mm365_helper_get_themeoption','meeting_types');
             $find_icon     = array_search($meeting_type, array_column($meeting_types, 'meeting_type_title'));
             ?>
             <img width="150px" class="pto-10" src="<?php echo $meeting_types[$find_icon]['meeting_icon'] ?>" alt="<?php echo $meeting_type; ?>"><br/>
             <?php echo $meeting_type; ?>
         </div>
         <div class="col-md-3" data-intro="Meeting time as agreed by both parties">
             <label for="">Meeting time</label><br/>
             <div class="time-slot">
             <p><?php 
                $accepted_slot = get_post_meta( $mid, 'mm365_accepted_meeting_slot', true );
                if($accepted_slot != 4):
                    // //Adjust array position 
                    $array_pos      = ($accepted_slot - 1);
                    $slot           = get_post_meta( $mid, 'mm365_meeting_slots');
                    $accepted_date  = explode("|",$slot[$array_pos]);

                    //TimeZone conversion
                    $date              =  apply_filters('mm365_meetings_convert_time', $accepted_date[0], $proposer_timezone, $viewer_timezone , 'm/d/Y' );
                    $from              =  apply_filters('mm365_meetings_convert_time', $accepted_date[0], $proposer_timezone, $viewer_timezone , 'h:i A' );
                    $to                =  apply_filters('mm365_meetings_convert_time', $accepted_date[1], $proposer_timezone, $viewer_timezone , 'h:i A' );

                    echo '<i class="far fa-calendar" aria-hidden="true"></i>'.$date."<br/><i class='far fa-clock' aria-hidden='true'></i>".$from." - ".$to." <br/><small>Converted to ".$viewer_timezone." timezone</small><br/>"; 
                    $time_diff = apply_filters('mm365_meetings_time_to_timestamp',$date,$from,$to,'difference'); 
                    echo "<i class='far fa-hourglass' aria-hidden='true'></i>".$time_diff['hours']."h ".$time_diff['minutes']."m";

                    ?>
                    <!-- Calendar integration -->
                    <div class="atcb" style="display:none;">
                    {
                    "name":"<?php echo get_post_meta( $mid, 'mm365_meeting_title', true ); ?>",
                    "description":"<?php echo wp_strip_all_tags(get_post_meta( $mid, 'mm365_meeting_details', true )); ?>:<br> For more details → [url]https://matchmaker365.org[/url]",
                    "startDate":"<?php echo apply_filters('mm365_meetings_convert_time', $accepted_date[0], $proposer_timezone, $proposer_timezone , 'Y-m-d' ) ?>",
                    "endDate":"<?php echo apply_filters('mm365_meetings_convert_time', $accepted_date[0], $proposer_timezone, $proposer_timezone , 'Y-m-d' ) ?>",
                    "startTime":"<?php echo apply_filters('mm365_meetings_convert_time', $accepted_date[0], $proposer_timezone, $proposer_timezone , 'H:i' ) ?>",
                    "endTime":"<?php echo apply_filters('mm365_meetings_convert_time', $accepted_date[1], $proposer_timezone, $proposer_timezone , 'H:i' ) ?>",
                    "location":"<?php get_post_meta( $mid, 'mm365_meeting_type', true ) ?>",
                    "options":["Apple","Google","iCal","Microsoft365","MicrosoftTeams","Outlook.com","Yahoo"],
                    "timeZone":"<?php echo $proposer_timezone; ?>",
                    "iCalFileName":"Matchmaker365 Meeting Event"
                    }
                    </div>
                    <!-- Calendar integration -->
                    <?php
                else:

                    $rescheduled_requested_to = get_post_meta( $mid, 'mm365_meeting_reschedule_timestamp', true );
                    $proposed_date = explode("|",$rescheduled_requested_to);

                    //$attendee_timezone = get_post_meta( $mid, 'mm365_attendee_timezone', true );
                    $date              =  apply_filters('mm365_meetings_convert_time', $proposed_date[0], $attendee_timezone, $viewer_timezone , 'm/d/Y' );
                    $from              =  apply_filters('mm365_meetings_convert_time', $proposed_date[0], $attendee_timezone, $viewer_timezone , 'h:i A' );
                    $to                =  apply_filters('mm365_meetings_convert_time', $proposed_date[1], $attendee_timezone, $viewer_timezone , 'h:i A' );

                    echo '<i class="far fa-calendar" aria-hidden="true"></i>'.$date."<br/><i class='far fa-clock' aria-hidden='true'></i>".$from." - ".$to." <br/><small>Converted to ".($viewer_timezone)." timezone</small><br/>"; 
                    $time_diff = apply_filters('mm365_meetings_time_to_timestamp',$date,$from,$to,'difference'); 
                    echo "<i class='far fa-hourglass' aria-hidden='true'></i>".$time_diff['hours']."h ".$time_diff['minutes']."m";

                    ?>
                    <!-- Calendar integration -->
                    <div class="atcb" style="display:none;">
                    {
                    "name":"<?php echo get_post_meta( $mid, 'mm365_meeting_title', true ); ?>",
                    "description":"<?php echo wp_strip_all_tags(get_post_meta( $mid, 'mm365_meeting_details', true )); ?>:<br> For more details → [url]https://matchmaker365.org[/url]",
                    "startDate":"<?php echo apply_filters('mm365_meetings_convert_time', $proposed_date[0], $attendee_timezone, $attendee_timezone , 'Y-m-d' ) ?>",
                    "endDate":"<?php echo apply_filters('mm365_meetings_convert_time', $proposed_date[0], $attendee_timezone, $attendee_timezone , 'Y-m-d' ) ?>",
                    "startTime":"<?php echo apply_filters('mm365_meetings_convert_time', $proposed_date[0], $attendee_timezone, $attendee_timezone , 'H:i' ) ?>",
                    "endTime":"<?php echo apply_filters('mm365_meetings_convert_time', $proposed_date[1], $attendee_timezone, $attendee_timezone , 'H:i' ) ?>",
                    "location":"<?php get_post_meta( $mid, 'mm365_meeting_type', true ) ?>",
                    "options":[
                        "Apple",
                        "Google",
                        "iCal",
                        "Microsoft365",
                        "MicrosoftTeams",
                        "Outlook.com",
                        "Yahoo"
                    ],
                    "timeZone":"<?php echo $attendee_timezone; ?>",
                    "iCalFileName":"Matchmaker365 Meeting Event"
                    }
                   </div>
                   <!-- Calendar integration -->
                    <?php

                endif;

             ?>
                   
             
             </p>
             </div>
         </div>
         <div class="col-md-6" data-intro="Details of the meeting like meeting URL or map location link along with instructions">
             <label for="">Meeting details</label><br/>
             <div class="meeting_details_block">
             <?php 
                echo $meeting_details;
             ?>
             <?php if($meeting_mode == 'scheduled' AND in_array($meeting_status,array('scheduled','rescheduled'))): ?>
                <a data-intro="You can edit the meeting details even after scheduling. The supplier will be notified about the change through an email" class="text-danger" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'edit_meeting_details' ), site_url().'/edit-meeting-details?mid='.$mid ); ?>"><i class="fas fa-arrow-up"></i> Edit Details</a>
             <?php endif; ?>   
             </div>
         </div>
     </div>
  <?php endif; endif; ?>


  <!-- Show active rescheduled info -->
  <?php 
  $rescheduled_by = get_post_meta($mid, 'mm365_meeting_active_reschedule_by', true); 
  if($rescheduled_by  != ''):
    switch ($rescheduled_by) {
        case 'proposer':
            $by = get_post_meta($mid, 'mm365_proposed_company', true);
            break;
        
        default:
            $by = get_post_meta($mid, 'mm365_meeting_with_company', true);
            break;
    }
  ?>
  <div class="form-row pto-10">
     <div class="col-12">
     <label for="">This meeting is rescheduled by '<?php echo $by; ?>' for the following reason</label><br/>
     <?php echo get_post_meta($mid, 'mm365_meeting_active_reschedule_reason', true); ?>
     <div>
  </div>
<?php endif; ?>


  <?php if($meeting_status == 'declined'): ?>
    <div class="row pbo-20 pto-20">
         <div class="col-md-12">
             <label for="">Reason for decline</label><br/>
             <?php echo get_post_meta( $mid, 'mm365_invite_declined_message', true ); ?>
         </div>
     </div>
  <?php endif; ?>


  <?php if($meeting_status == 'cancelled'): ?>
    <div class="row pto-20">
         <div class="col-md-12">
             <label for="">Reason for cancellation</label><br/>
             <?php echo get_post_meta( $mid, 'mm365_meeting_termination_message', true ); ?>
         </div>
     </div>
  <?php endif; ?>

  <?php if($meeting_status == 'meeting_declined'): ?>
    <div class="row pto-20 pbo-20">
         <div class="col-md-12" data-intro="Reason provided by the company for declining this meeting">
             <label for="">Reason for decline</label><br/>
             <?php echo get_post_meta( $mid, 'mm365_meeting_termination_message', true ); ?>
         </div>
     </div>
  <?php endif; ?>



<!-- schedulers action block  Meeting setup form -->
<?php if($meeting_mode == 'scheduled' AND in_array($meeting_status,array('accepted','proposed_new_time'))): ?>
<form method="post" id="mm365_meeting_scheduling" action="#"  data-parsley-validate enctype="multipart/form-data" >
<div class="form-row form-group pto-30">
  <div class="col-lg-4" data-intro="You can choose any of the digital meeting platform or direct meeting and update the meeting details (Meeting link URLs or Map location urls and other instructions if any) in the 'Meeting details'">
        <label for="">Meeting Type<span>*</span></label>
        <select name="meeting_type" id="meeting_type" required class="form-control mm365-single-image" data-parsley-errors-container=".meetingtypeError">
                <option data-img_src="<?php echo esc_attr(get_template_directory_uri( )) ?>/assets/images/empty.png" value="">-Select-</option>
                <?php
                $meeting_types = apply_filters('mm365_helper_get_themeoption','meeting_types'); 
                foreach ($meeting_types as $key => $value) {
                    if($value['meetingtype_display_mode'] == 1){
                      echo '<option data-img_src="'.$value['meeting_icon'].'">'.$value['meeting_type_title'].'</option>';
                    }
                }
                ?>
        </select>
        <div class="meetingtypeError"></div>
        <!--  Show selected icons here -->
        <div id="meeting_icon"></div>


  </div>
  <div class="col-12 d-block d-sm-none pbo-30"></div>
  <div class="col-lg-8" data-intro="Deatils about how the meeting will happen. Meeting link URL , Map location URL or other instructions if any. ">
        <label for="">Meeting details<span>*</span></label>
        <textarea class="form-control" required name="meeting_details" id="meeting_details" cols="30" rows="3" data-parsley-errors-container=".meetingError"></textarea>
        <div class="meetingError"></div>
  </div>
</div>
<div class="form-row pto-0">
        <div class="col text-right d-flex flex-column flex-md-row-reverse">
            <?php $url_cancel   = site_url().'/cancel-meeting?mid='.$mid; ?>
            <a href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'cancel_meeting' ), $url_cancel ); ?>" data-intro="If you are not proceeding with the meeting, click 'Cancel'" class="btn btn-primary red">Cancel this meeting</a>&nbsp;&nbsp;
            <input type="hidden" name="meeting_id" id="meeting_id" value="<?php echo esc_html($mid); ?>">
            <button type="submit" class="btn btn-primary" ><?php _e('Submit', 'mm365') ?></button>
        </div>
</div>
</form>
<?php endif; ?>
<!-- schedulers action block  Meeting setup form ends here -->

  <?php 
   $mr_id        = get_post_meta( $mid, 'mm365_from_matchrequest', true );
   $match_status = get_post_meta( $mr_id, 'mm365_matchrequest_status', true );
  
  if($meeting_mode == 'invited' AND !in_array($meeting_status,array('proposed','meeting_declined','cancelled','declined'))): ?>
  <!-- Conditional buttons -->
    <div class="form-row pto-0">
        <div class="col text-right d-flex flex-column flex-md-row-reverse">
            <?php
            //get match status and display buttons accordingly
            if($match_status != 'closed'){
            if($meeting_status == 'scheduled' OR $meeting_status == 'rescheduled'): ?>
            <a data-intro="Both parties can reschedule the meeting in case of unavoidable situation." href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'reschedule_meeting' ), site_url().'/reschedule-meeting?mid='.$mid ); ?>" class="btn btn-primary">Reschedule this meeting</a>
            <?php endif; ?>
            <a data-intro="Both parties can decline the meeting in case of unavoidable situation." href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'cancel_meeting' ), site_url().'/cancel-meeting?mid='.$mid ); ?>" class="btn btn-primary red">Decline this meeting</a>
            <?php } ?>
        </div>
    </div>
  <?php endif; ?>

  <?php if($meeting_mode == 'scheduled' AND in_array($meeting_status,array('proposed','scheduled','rescheduled'))): ?>
    <div class="form-row pto-0">
        <div class="col text-right d-flex flex-column flex-md-row-reverse d-flex flex-column flex-md-row-reverse">
            <?php 
            if($match_status != 'closed'){
            if($meeting_status == 'scheduled' OR $meeting_status == 'rescheduled'): ?>
            <a data-intro="Both parties can reschedule the meeting in case of unavoidable situation." href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'reschedule_meeting' ), site_url().'/reschedule-meeting?mid='.$mid ); ?>" class="btn btn-primary">Reschedule this meeting</a>&nbsp;&nbsp;
            <?php endif;  $url_cancel   = site_url().'/cancel-meeting?mid='.$mid; ?>
            <a data-intro="Both parties can cancel the meeting in case of unavoidable situation." href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'cancel_meeting' ), $url_cancel ); ?>" class="btn btn-primary red">Cancel this meeting</a>
            <?php } ?>
        </div>
    </div>
  <?php endif; ?>

     </section>

    </div>
</div>

<?php
get_footer();
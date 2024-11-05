<?php
/**
 * Template Name: SB - View Buyer Meeting
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['super_buyer']);

//Meeting id
$mid = $_REQUEST['mid'];

//Buyer

$buyer_cmp_id = get_post_meta($mid, 'mm365_proposed_company_id', true);
$supplier_cmp = get_post_meta($mid, 'mm365_meeting_with_company');
$supplier_cmp_id = $supplier_cmp[1];
$meeting_status = get_post_meta($mid, 'mm365_meeting_status', true);
get_header();

?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>

  <div class="dashboard-content-panel">

    <a href="#" onclick="location.replace(document.referrer)" class="">
      <h3 class='heading-large'><img class="back-arrow"
          src="<?php echo get_template_directory_uri() ?>/assets/images/arrow-left.svg" height="36px"
          alt="">&nbsp;Meeting Details</h3>
    </a>


    <section class="company_preview_ex">

      <div class="row pto-30">
        <div class="col-md-6">
          <h6>Buyer</h6>
          <hr />

          <div class="buyer-team-card std">
            <h3>
              <?php
              echo esc_html(get_the_title($buyer_cmp_id));
              ?>
            </h3>

            <div class="contact">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z">
                </path>
              </svg>
              <?php
              echo esc_html(get_post_meta($buyer_cmp_id, 'mm365_contact_person', true));
              ?>
            </div>

            <div class="contact">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z">
                </path>
              </svg>
              <?php
              echo esc_html(get_post_meta($buyer_cmp_id, 'mm365_company_phone', true));
              ?>
            </div>

            <div class="contact">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z">
                </path>
              </svg>
              <?php
              echo esc_html(get_post_meta($buyer_cmp_id, 'mm365_company_email', true));
              ?>
            </div>
          </div>



        </div>
        <div class="col-md-6">
          <h6>Supplier</h6>
          <hr />

          <div class="buyer-team-card std">
            <h3>
              <?php
              echo esc_html($supplier_cmp[0]);
              ?>
            </h3>

            <div class="contact">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z">
                </path>
              </svg>
              <?php
              echo esc_html(get_post_meta($supplier_cmp_id, 'mm365_contact_person', true));
              ?>
            </div>

            <div class="contact">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z">
                </path>
              </svg>
              <?php
              echo esc_html(get_post_meta($supplier_cmp_id, 'mm365_company_phone', true));
              ?>
            </div>

            <div class="contact">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z">
                </path>
              </svg>
              <?php
              echo esc_html(get_post_meta($supplier_cmp_id, 'mm365_company_email', true));
              ?>
            </div>
          </div>

        </div>
      </div>

      <div class="row pto-30">

        <div class="col-md-6">
          <h6>Meeting Title & Agenda</h6>
          <hr />
          <div class="buyer-team-card std">
            <h3>
              <?php
              echo esc_html(get_the_title($mid));
              ?>
            </h3>
            <?php
            echo (get_post_meta($mid, 'mm365_meeting_agenda', true));
            ?>
          </div>
        </div>

        <div class="col-md-6">
          <h6>Meeting Details</h6>
          <hr />
          <div class="buyer-team-card std">

            <?php if ($meeting_status) {
              echo "<div>Current meeting status is '<span class='meeting_status " . $meeting_status . "'>" . preg_replace('/\_+/', ' ', $meeting_status) . "</span>'.</div>";
            }
            ?>

            <!-- Show respective notices -->

                     <?php if ($meeting_status == 'declined'): ?>
                      <div class="row pbo-20 pto-20">
                        <div class="col-md-12">
                          <label for="">Reason for decline</label><br />
                          <?php echo get_post_meta($mid, 'mm365_invite_declined_message', true); ?>
                        </div>
                      </div>
                    <?php endif; ?>

                    <?php if ($meeting_status == 'cancelled'): ?>
                      <div class="row pto-20">
                        <div class="col-md-12">
                          <label for="">Reason for cancellation</label><br />
                          <?php echo get_post_meta($mid, 'mm365_meeting_termination_message', true); ?>
                        </div>
                      </div>
                    <?php endif; ?>

                    <?php if ($meeting_status == 'meeting_declined'): ?>
                      <div class="row pto-20 pbo-20">
                        <div class="col-md-12" data-intro="Reason provided by the company for declining this meeting">
                          <label for="">Reason for decline</label><br />
                          <?php echo get_post_meta($mid, 'mm365_meeting_termination_message', true); ?>
                        </div>
                      </div>
                    <?php endif; ?>

                    <?php if ($meeting_status == 'rescheduled'): ?>
                      <div class="row pto-20 pbo-20">
                        <div class="col-md-12" data-intro="Reason provided by the company for declining this meeting">
                          <label for="">Reason</label><br />
                          <?php echo get_post_meta($mid, 'mm365_meeting_active_reschedule_reason', true); ?>
                        </div>
                      </div>
                    <?php endif; ?>


            <?php 
            
            if(in_array($meeting_status, array('scheduled', 'rescheduled', 'accepted')))
            {
            
              $meeting_type = get_post_meta($mid, 'mm365_meeting_type', true);
              $meeting_details = get_post_meta($mid, 'mm365_meeting_details', true);
              $proposer_timezone = get_post_meta($mid, 'mm365_proposer_timezone', true);
             
             
                ?>
                <div class="row pto-20">
  
                <?php  if ($meeting_type != ''){ ?>
                  <div class="col-md-6"
                    data-intro="How the meeting is happeing. It can be any of the popular virtual meeting platforms or can be direct meeting.">
                    <label for="">Meeting type</label><br />
                    <?php
                        $meeting_types = apply_filters('mm365_helper_get_themeoption','meeting_types');
                       $find_icon = array_search($meeting_type, array_column($meeting_types, 'meeting_type_title'));
                    ?>
                    <img width="150px" class="pto-10" src="<?php echo $meeting_types[$find_icon]['meeting_icon'] ?>"
                      alt="<?php echo $meeting_type; ?>"><br />
                    <?php echo $meeting_type; ?>

                  </div>
                  <?php } ?>

                  <div class="col-md-6" data-intro="Meeting time as agreed by both parties">
                    <label for="">Meeting time</label><br />
                    <div class="time-slot">
                      <p>
                        <?php
                        $accepted_slot = get_post_meta($mid, 'mm365_accepted_meeting_slot', true);
                        if ($accepted_slot != 4){
                          // //Adjust array position 
                          $array_pos = ($accepted_slot - 1);
                          $slot = get_post_meta($mid, 'mm365_meeting_slots');
                          $accepted_date = explode("|", $slot[$array_pos]);

                          //TimeZone conversion
                          $date = apply_filters('mm365_meetings_convert_time', $accepted_date[0], $proposer_timezone, $proposer_timezone, 'm/d/Y');
                          $from = apply_filters('mm365_meetings_convert_time',$accepted_date[0], $proposer_timezone, $proposer_timezone, 'h:i A');
                          $to = apply_filters('mm365_meetings_convert_time',$accepted_date[1], $proposer_timezone, $proposer_timezone, 'h:i A');

                          echo '<i class="far fa-calendar" aria-hidden="true"></i>' . $date . "<br/><i class='far fa-clock' aria-hidden='true'></i>" . $from . " - " . $to . " <br/><small>Timezone:" . $proposer_timezone . " timezone</small><br/>";
                          $time_diff = apply_filters('mm365_meetings_time_to_timestamp',$date,$from,$to,'difference');
                          echo "<i class='far fa-hourglass' aria-hidden='true'></i>" . $time_diff['hours'] . "h " . $time_diff['minutes'] . "m";

                          ?>
                          <!-- Calendar integration -->
                        <div class="atcb" style="display:none;">
                          {
                          "name":"
                          <?php echo get_post_meta($mid, 'mm365_meeting_title', true); ?>",
                          "description":"
                          <?php echo wp_strip_all_tags(get_post_meta($mid, 'mm365_meeting_details', true)); ?>:<br> For more
                          details → [url]https://matchmaker365.org[/url]",
                          "startDate":"
                          <?php echo apply_filters('mm365_meetings_convert_time',$accepted_date[0], $proposer_timezone, $proposer_timezone, 'Y-m-d') ?>",
                          "endDate":"
                          <?php echo apply_filters('mm365_meetings_convert_time',$accepted_date[0], $proposer_timezone, $proposer_timezone, 'Y-m-d') ?>",
                          "startTime":"
                          <?php echo apply_filters('mm365_meetings_convert_time',$accepted_date[0], $proposer_timezone, $proposer_timezone, 'H:i') ?>",
                          "endTime":"
                          <?php echo apply_filters('mm365_meetings_convert_time',$accepted_date[1], $proposer_timezone, $proposer_timezone, 'H:i') ?>",
                          "location":"
                          <?php get_post_meta($mid, 'mm365_meeting_type', true) ?>",
                          "options":["Apple","Google","iCal","Microsoft365","MicrosoftTeams","Outlook.com","Yahoo"],
                          "timeZone":"
                          <?php echo $proposer_timezone; ?>",
                          "iCalFileName":"Matchmaker365 Meeting Event"
                          }
                        </div>
                        <!-- Calendar integration -->
                        <?php
                         }
                        else{

                          $rescheduled_requested_to = get_post_meta($mid, 'mm365_meeting_reschedule_timestamp', true);
                          $proposed_date = explode("|", $rescheduled_requested_to);

                          $attendee_timezone = get_post_meta($mid, 'mm365_attendee_timezone', true);
                          $date = apply_filters('mm365_meetings_convert_time',$proposed_date[0], $attendee_timezone, $attendee_timezone, 'm/d/Y');
                          $from = apply_filters('mm365_meetings_convert_time',$proposed_date[0], $attendee_timezone, $attendee_timezone, 'h:i A');
                          $to = apply_filters('mm365_meetings_convert_time',$proposed_date[1], $attendee_timezone, $attendee_timezone, 'h:i A');

                          echo '<i class="far fa-calendar" aria-hidden="true"></i>' . $date . "<br/><i class='far fa-clock' aria-hidden='true'></i>" . $from . " - " . $to . " <br/><small>Timezone: " . $attendee_timezone . " timezone</small><br/>";
                          $time_diff = apply_filters('mm365_meetings_time_to_timestamp',$date,$from,$to,'difference');
                          echo "<i class='far fa-hourglass' aria-hidden='true'></i>" . $time_diff['hours'] . "h " . $time_diff['minutes'] . "m";

                          ?>
                        <!-- Calendar integration -->
                        <div class="atcb" style="display:none;">
                          {
                          "name":"
                          <?php echo get_post_meta($mid, 'mm365_meeting_title', true); ?>",
                          "description":"
                          <?php echo wp_strip_all_tags(get_post_meta($mid, 'mm365_meeting_details', true)); ?>:<br> For more
                          details → [url]https://matchmaker365.org[/url]",
                          "startDate":"
                          <?php echo apply_filters('mm365_meetings_convert_time',$proposed_date[0], $attendee_timezone, $attendee_timezone, 'Y-m-d') ?>",
                          "endDate":"
                          <?php echo apply_filters('mm365_meetings_convert_time',$proposed_date[0], $attendee_timezone, $attendee_timezone, 'Y-m-d') ?>",
                          "startTime":"
                          <?php echo apply_filters('mm365_meetings_convert_time',$proposed_date[0], $attendee_timezone, $attendee_timezone, 'H:i') ?>",
                          "endTime":"
                          <?php echo apply_filters('mm365_meetings_convert_time',$proposed_date[1], $attendee_timezone, $attendee_timezone, 'H:i') ?>",
                          "location":"
                          <?php get_post_meta($mid, 'mm365_meeting_type', true) ?>",
                          "options":[
                          "Apple",
                          "Google",
                          "iCal",
                          "Microsoft365",
                          "MicrosoftTeams",
                          "Outlook.com",
                          "Yahoo"
                          ],
                          "timeZone":"
                          <?php echo $attendee_timezone; ?>",
                          "iCalFileName":"Matchmaker365 Meeting Event"
                          }
                        </div>
                        <!-- Calendar integration -->
                        <?php

                        }
                      }
                    
                        ?>
                      </p>
                    </div>

                  </div>


                </div>


          </div>
        </div>
      </div>


    </section>


  </div>
</div>

<?php
get_footer();
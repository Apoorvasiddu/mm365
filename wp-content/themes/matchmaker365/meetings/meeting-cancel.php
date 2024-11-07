<?php
/**
 * Template Name: Meeting Cancel
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['business_user']);

// //Check if user has active registration else redirect
do_action('mm365_helper_check_companyregistration', 'register-your-company');

$company_id = $_COOKIE['active_company_id'];


$mid            = $_REQUEST['mid'];
$nonce          = $_REQUEST['_wpnonce'];
// $meeting_class  = new mm365_meetings;
$wp_post_status = get_post_status($mid);
$meeting_mode   = apply_filters('mm365_meetings_ownership_check',$mid,$company_id);
$meeting_status = get_post_meta( $mid, 'mm365_meeting_status',true);

if($meeting_mode == 'invited'){

   if(in_array($meeting_status,array("declined","cancelled","meeting_declined"))){
    wp_redirect(site_url()."/meeting-invites");
   }

    $info           = get_post_meta( $mid, 'mm365_proposed_company');
    $with_company   = $info[0];
    $contact_person = $info[1];
    $email          = $info[2];
    $heading        = 'Decline';
    $txtara_heading = 'decline';
    $txtara_hp      = 'declining';
}elseif($meeting_mode == 'scheduled'){
  if(in_array($meeting_status,array("declined","cancelled","meeting_declined"))){
    wp_redirect(site_url()."/meetings-scheduled");
   }
    $info           = get_post_meta( $mid, 'mm365_meeting_with_company');
    $with_company   = $info[0];
    $heading        = 'Cancel';
    $txtara_heading = 'cancellation';
    $txtara_hp      = 'cancelling';
}

if (($meeting_mode == 'unauth' OR $wp_post_status != 'publish')) {
    die( __( 'Unauthorised access', 'mm365' ) ); 
}


//Invite
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
        <?php echo $heading; ?>  Meeting <?php echo " - ".$with_company; ?></h1>
        <section class="company_preview">
             <form method="post" id="mm365_meeting_terminate" action="#"  data-parsley-validate enctype="multipart/form-data" >
             <div class="form-row form-group">
                <div class="col-lg-12">
                    <label for="">Reason for <?php echo $txtara_heading; ?><span>*</span><br/><small>Please enter the reason for <?php echo $txtara_hp; ?> the meeting</small></label>
                    
                    <textarea name="terminate_meeting_message" id="terminate_meeting_message" class="form-control" id=""  required rows="2"></textarea>
                </div>
             </div>            
             <div class="form-row">
                    <div class="col-md-12 text-right">
                            <input type="hidden" name="meeting_mode" id="meeting_mode" value="<?php echo $meeting_mode; ?>">
                            <input type="hidden" name="meeting_id" id="meeting_id" value="<?php echo $mid; ?>">
                            <input type="hidden" name="redirect_to" id="redirect_url" value="<?php echo esc_url(site_url().'/meeting-details?mid='.$mid); ?>">
                            <button type="submit" class="btn btn-primary" ><?php _e('Submit', 'mm365') ?></button>
                    </div>
             </div>
            </form>
        </section>
    </div>
  </div>
</div>

<?php
get_footer();
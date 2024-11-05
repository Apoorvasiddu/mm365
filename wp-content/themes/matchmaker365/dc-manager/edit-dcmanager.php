<?php
/**
 * Template Name: SA - Edit Council Manager
 *
 */
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

$cmu_id     = $_REQUEST['cmu'];
$nonce      = $_REQUEST['_wpnonce'];

$user_details = get_userdata($cmu_id );

if (!wp_verify_nonce( $nonce, 'sa_edit_council_manager' ) OR empty($user_details)) {
    die( __( 'Unauthorised action', 'mm365' ) ); 
    //wp_safe_redirect( site_url()."/list-council-managers" );
    //exit;
 
}

$users_council =  apply_filters('mm365_helper_get_usercouncil',$cmu_id);
get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
  </div>
  <div class="dashboard-content-panel">

<h1 class="heading-large pbo-10"><a onclick="history.back();" href="#"><img class="back-arrow" src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow-left.svg" height="36px" alt=""></a> Edit Council Manager</h1>


<!-- Request for match form -->
<form method="post" id="mm365_update_dc_manager" action="#"  data-parsley-validate enctype="multipart/form-data" >
  <section class="company_preview">
  <div class="form-row form-group">
        <div class="col-lg-4" data-intro="Email address of the user. This email address can be changed">
                <label for="">Email<span>*</span></label>
                <input placeholder="Please enter email" class="form-control"  type="email" required name="dcm_email" id="mm365_dcm_email" value="<?php echo esc_html($user_details->user_email); ?>"> 
                <span class="check-icon">
                    <img  src="<?php echo get_template_directory_uri() ?>/assets/images/red-tick.svg" id="email-check-fail" alt="" >
                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/green-tick.svg" id="email-check-success" alt="">
                </span>
        </div>   
        <div class="col-lg-4" data-intro="Username cannot be changed">
                <label for="">Username<span>*</span></label>
                <input placeholder="Please enter username" class="form-control"  type="text" required readonly name="dcm_username" id="mm365_dcm_username" value="<?php echo esc_html($user_details->user_login); ?>"> 
                
        </div>    
  </div>
  <div class="form-row form-group" data-intro="First name, last name and phone number">
        <div class="col-lg-4">
                <label for="">First Name<span>*</span></label>
                <input placeholder="" class="form-control"  type="text" required name="dcm_first_name" name="dcm_first_name" pattern="[a-zA-Z\s]+" minlength="2" value="<?php echo esc_html($user_details->first_name); ?>"> 
        </div>   
        <div class="col-lg-4">
                <label for="">Last Name<span>*</span></label>
                <input placeholder="" class="form-control"  type="text" required name="dcm_last_name" name="dcm_last_name" pattern="[a-zA-Z\s]+" minlength="2" value="<?php echo esc_html($user_details->last_name); ?>"> 
        </div>    
        <div class="col-lg-4">
                <label for="">Phone<span>*</span></label>
                <input placeholder="" class="form-control"  type="text" required name="dcm_phone"  pattern="[0-9+()\s]+" data-parsley-length="[6, 15]"  data-parsley-length-message="The phone number should be 6 to 15 digits long" value="<?php echo esc_html(get_user_meta($cmu_id, '_mm365_dcm_phone', true )); ?>"> 
        </div> 
  </div>
  <div class="form-row form-group">
        <div class="col-lg-8" data-intro="Associated council can be changed">
                <label for="">Council<span>*</span></label>
                <select required name="dcm_council_id" id="dcm_council_id" class="form-control">
                        <?php 
                        apply_filters('mm365_dropdown_councils', $users_council);
                        ?>
                </select>
        </div>    
        <div class="col-lg-4" data-intro="This is user's login status. If you want to block the user from accessing the platform, change the status to INACTIVE">
                <label for="">Status</label><br/>
                <?php 
                $user_lock_status = get_user_meta($cmu_id, 'baba_user_locked', true ); 
                
                ?>
                <input type="radio" name="login_stat" id="" value="" <?php echo ($user_lock_status == '') ?  "checked" :  ""; ?>> Active 
                &nbsp;&nbsp;&nbsp;<input type="radio" name="login_stat" id="" value="yes" <?php echo ($user_lock_status == 'yes') ?  "checked" :  "";?>> Inactive 
        </div> 
  </div>
  <div class="form-row pto-30">
      <div class="col text-right">
            <?php wp_nonce_field( 'post_nonce', 'post_nonce_field' ); ?>
            <input type="hidden" name="cmu_id" id="cmu_id" value="<?php echo  esc_html($cmu_id); ?>" />
            <input type="hidden" name="current_user" id="current_user" value="<?php echo  esc_html($user->ID); ?>" />
            <input type="hidden" id="after_success_redirect" name="after_success_redirect" value="<?php echo esc_url(site_url()."/list-council-managers");?>">
            <button id="sa_dcm_add" type="submit" class="btn btn-primary" ><?php _e('Update', 'mm365') ?></button>
      </div>
  </div>

  </section>
</form>




  </div><!-- dash panel -->
</div><!--dash -->

<?php
get_footer();
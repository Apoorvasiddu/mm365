<?php
/**
 * Template Name: Certificate Details
 *
 */

$user = wp_get_current_user();
if(is_user_logged_in() AND in_array( 'business_user', (array) $user->roles )){
$cert           = $_REQUEST['cert'];
$nonce          = $_REQUEST['_wpnonce'];

if ( ! wp_verify_nonce( $nonce, 'certificate_details' ) OR get_post_type($cert) != 'mm365_certification') {
    //die( __( 'Unauthorised action', 'mm365' ) ); 
    wp_safe_redirect( site_url()."/certificate-upload" );
    exit;
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
        <a href="#" onclick="history.back();"><img class="back-arrow" src="<?php echo get_template_directory_uri()?>/assets/images/arrow-left.svg" height="36px" alt=""></a>
        MBE Certificate</h1>
        <section class="company_preview">
             <div class="row">
                <div class="col-lg-2">
                   <label>Status</label>
                   <p><?php $status = get_post_meta($cert, 'mm365_certificate_status',true);?>
                   <span class='meeting_status <?php echo esc_attr($status); ?>'><?php echo preg_replace('/\_+/', ' ', $status); ?></span>
                   </p>
                </div>
                <div class="col-lg-2">
                  <label>Date uploaded</label>
                  <p><?php echo get_the_date("m/d/Y", $cert); ?></p>
                </div>
                <div class="col-lg-2">
                   <label>Expiration date</label>
                   <p><?php echo get_post_meta($cert, 'mm365_expiry_date',true); ?></p>
                   
                </div>
                <?php if(get_post_meta($cert, 'mm365_certificate_status',true) == 'pending'): ?>
                  <div class="col-lg-6 text-right">
                  <a href="#" class="btn btn-primary red delete-certificate" data-certificate="<?php echo esc_attr($cert) ?>" data-redirect="<?php echo esc_url(site_url()."/certificate-upload"); ?>">Delete Certificate</a>
                  </div>
                <?php endif; ?>

             </div>
            <?php 
            $note = get_post_meta($cert, 'mm365_admin_note',true);
            if($note != ''):
            ?>
            <div class="row">
              <div class="col-lg-12">
                <label>Notes/remarks from admin</label>
                <p><?php echo esc_html($note); ?></p>
              </div>
            </div>
            <?php endif; ?>

             <div class="row">
                <div class="col-lg-5 pto-30">
                <label>Certificate
                <br/><small>Click here to view the certificate</small></label><br/>
                <?php
                    $certificate  = get_post_meta($cert, 'mm365_certificate',true);
                    foreach($certificate as $key => $value){
                              if(get_post_mime_type($key) != 'application/pdf'){
                                  echo '<a data-fancybox href="'.$value.'"><img src="'.$value.'" alt="Certificate" class="img-fluid"></a>';
                              }else{
                                echo '<a data-fancybox href="'.$value.'"><img src="'.get_stylesheet_directory_uri( ).'/assets/images/pdf.svg" alt="Certificate" width="70px" height="70px"><br/>'.basename ( get_attached_file( $key ) ).'</a>';
                              }
                    }
                ?>

                </div>
             </div>
        </section>
    </div>
  </div>
</div>
<?php } else {       
      $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      wp_redirect(wp_login_url($actual_link));?>
<h2>Please sign in to continue</h2>
<?php } ?>
<?php
get_footer();
<?php
/**
 * Template Name: Admin - Certificate Details
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['business_user','council_manager','mmsdc_manager']);

$cert           = $_REQUEST['cert'];
$nonce          = $_REQUEST['_wpnonce'];


(isset($_REQUEST['edit'])) ? $edit = $_REQUEST['edit']: $edit = "";

if ( ! wp_verify_nonce( $nonce, 'admin_certificate_details' ) ) {
    die( __( 'Unauthorised token', 'mm365' ) ); 
}

//Redirect if user trying to acess wrong post from differnt post type 
//MMSDC_ADMIN aka Super admin has full acess
$council_id = apply_filters('mm365_helper_get_usercouncil',$user->ID);
if($council_id != ''){
 apply_filters('mm365_certification_can_council_access',$cert, $council_id, 'certificate-verification');
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
        Verify MBE Certificate</h1>
        <section class="company_preview">
             <div class="row">
                <div class="col-lg-2" data-intro=" Certificate - Current Status">
                   <label>Status</label>
                   <p><?php $status = get_post_meta($cert, 'mm365_certificate_status',true);?>
                   <span class='meeting_status <?php echo esc_attr($status); ?>'><?php echo preg_replace('/\_+/', ' ', $status); ?></span>
                   </p>
                </div>
                <div class="col-lg-2" data-intro="Submitted company">
                   <label>Submitted by</label>
                   <p> <?php $submitted = get_post_meta($cert,'mm365_submitted_by',true); ?>
                   <a href="<?php echo site_url(); ?>/view-company?cid=<?php echo esc_html($submitted); ?>"><?php echo esc_html(get_the_title($submitted)); ?></a>
                   </p>
                </div>
                <div class="col-lg-2" data-intro="Date of upload">
                  <label>Date uploaded</label>
                  <p><?php echo get_the_date("m/d/Y", $cert); ?></p>
                </div>
                <div class="col-lg-2" data-intro="Date of expiry">
                   <label>Expiration date</label>
                   <p><?php echo get_post_meta($cert, 'mm365_expiry_date',true); ?></p>

                </div>
                <div class="col-lg-4" >
                <?php $note = get_post_meta($cert, 'mm365_admin_note',true);
                    if($note != ''):
                    ?>
                     <label>Notes/remarks</label>
                     <p><?php echo esc_html($note); ?></p>
                <?php endif; ?>

                </div>
             </div>
             <div class="row">
                <div class="col-lg-5" data-intro=" Certificate submitted by the user">
                <label>Certificate<br/><small>Click here to view the certificate</small></label><br/>
                <?php
                    $certificate  = get_post_meta($cert, 'mm365_certificate',true);
                    if(!empty($certificate)){
                     foreach($certificate as $key => $value){
                           if(get_post_mime_type($key) != 'application/pdf'){
                              echo '<a target="_black" href="'.$value.'"><img src="'.$value.'" alt="Certificate" class="img-fluid"></a>';
                           }else{
                              echo '<a target="_black" href="'.$value.'"><img src="'.get_stylesheet_directory_uri( ).'/assets/images/pdf.svg" alt="Certificate" width="70px" height="70px"><br/>'.basename ( get_attached_file( $key ) ).'</a>';
                           }
                      }
                     }
                ?>

                </div>
             </div>

            <!-- Action Block | Check permissions here -->
           <?php if($status == 'pending' OR $edit == 1 AND $status != 'expired' ): ?>
            <form method="post" id="mm365_admin_certificate_action" action="#"  data-parsley-validate enctype="multipart/form-data" >
            <div class="form-row form-group pto-30">
              <div class="col-lg-12" data-intro="Please add your note/remarks then click 'Verify' to accept the certificate or 'Unapprove' to reject the certificate">
                    <label for="">Notes/Remarks<span>*</span></label>
                    <textarea class="form-control" required name="certificate_note" id="certificate_note" cols="30" rows="3" data-parsley-errors-container=".certnoteError"></textarea>
                    <div class="certnoteError"></div>
              </div>
            </div>
            <div class="form-row pto-0">
                    <div class="col text-right">
                       <input type="hidden" name="certificate_id" id="certificate_id" value="<?php echo esc_html($cert); ?>">
                       <input type="hidden" name="redirect_url" id="redirect_url" value="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'admin_certificate_details' ), site_url().'/admin-certificate-details?cert='.$cert); ?>">
                       <?php if($status != 'unapproved'): ?>
                       <button type='button' id="reject-certificate" data-status="rejected" class="btn btn-primary red" data-intro="If the certificate or expiry date is invalid, please do click this button"><?php _e('Unapprove', 'mm365') ?></button>
                       <?php endif; if($status != 'verified'):?>
                       <button type='submit' id="approve-certificate" data-status="verified" class="btn btn-primary green" data-intro="Click this button if the cerificate and expiry date is valid and acceptable"><?php _e('Verify', 'mm365') ?></button>
                       <?php endif; ?>
                     </div>
            </div>
            </form>
            <?php elseif($status != 'expired'): ?>
               <section class="form-row form-group pto-30">
                 <div class="col-lg-12 text-right">
                      <a data-intro="If you want to revise the approval status, click this button" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'admin_certificate_details' ), site_url().'/admin-certificate-details?edit=1&cert='.$cert); ?>#mm365_admin_certificate_action" class="btn btn-primary">Edit</a>
                  </div>
               </section>
            <?php endif; ?>
        </section>
    </div>
  </div>
</div>
<?php
get_footer();
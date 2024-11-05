<?php
/**
 * Template Name: Admin - Match Preference Changelog
 *
 */
$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole',['council_manager','mmsdc_manager']);

$nonce      = $_REQUEST['_wpnonce'];
if ( ! wp_verify_nonce( $nonce, 'matchpref_log' ) ) {
    die( __( 'Unauthorised token', 'mm365' ) ); 
}

//Company ID
$company_id      = $_REQUEST['cid'];
$changelog = get_post_meta(sanitize_text_field($company_id), 'mm365_approval_required_feature_history');
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
     Match preference changelog</h1>
     <section class="company_preview">
            <h5>Company name: <?php echo get_the_title($company_id); ?></h5>
            <?php if(count($changelog) > 0): ?>
            <table class="matchrequests-list table table-striped dataTable no-footer dtr-inline">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Auto Approval</th>
                    <th scope="col">Changed by</th>
                </tr>
                </thead>
                <tbody>

                <?php
                krsort($changelog);
                foreach ($changelog as $log) {
                    
                    $data  = explode("|",$log);
                ?>
                  <tr>
                    <td><?php echo date('m/d/Y h:i a',$data[0]); ?></td>
                    <td><?php echo '<span class="approval-required '.$data[1].'">&nbsp;'.$data[1].'</span>'; ?></td>
                    <td><?php echo get_userdata($data[2])->user_login; ?></td>                    
                  </tr>
                <?php } ?>
                </tbody>
            </table>
           <?php else: ?>
              <h4 class="text-danger">No records found!</h4>
           <?php endif; ?>
     </section>
  </div>
</div>

 <?php  get_footer();
<?php
/**
 * Template Name: Match Request Preview
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['business_user', 'council_manager','mmsdc_manager']);
//ATTENTION HERE
$mr_id = $_REQUEST['mr_id'];
//Redirect if acessing wrong post type
// if(in_array( 'council_manager', (array) $user->roles )){
//   $mm365_helper->is_right_post($mr_id,'mm365_matchrequests','council-match-requests');
// }

get_header();
?>
<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>
  <section class="dashboard-content-panel">
    <!-- Panel starts -->

    <!-- <h1 class="heading-large pbo-20">Match Request Details</h1> -->
    <?php

    //Check if post belongs to current user
    if (get_current_user_id() == get_post_field('post_author', $mr_id)) {
      apply_filters('mm365_matchrequest_show', $mr_id, 'publish');
    } elseif (in_array('mmsdc_manager', (array) $user->roles)) {
      ?>
      <div class="pbo-20">
        <a href="<?php echo site_url(); ?>/admin-matchrequests-listing#mr" class="">
          <h3 class='heading-large d-inline'>
            <img class="back-arrow" src="<?php echo get_template_directory_uri() ?>/assets/images/arrow-left.svg"
              height="36px" alt="">&nbsp;Match Request Details
          </h3>
        </a>
        <?php //$mm365_helper->matchstatus_display($mr_id); ?>
      </div>
      <?php
      //mm365_matchrequests_admin_preview($mr_id,'publish');
      apply_filters('mm365_matchrequest_admin_preview', $mr_id, 'publish');
    } elseif (in_array('council_manager', (array) $user->roles)) {
      ?>
      <a href="<?php echo site_url(); ?>/council-match-requests" class="">
        <h3 class='heading-large pbo-20'>
          <img class="back-arrow" src="<?php echo get_template_directory_uri() ?>/assets/images/arrow-left.svg"
            height="36px" alt="">
          &nbsp;Match Request Details
        </h3>
      </a>
      <?php
      $council_id = apply_filters('mm365_helper_get_usercouncil', $user->ID);
      $can_access = apply_filters('mm365_council_content_access_check', $mr_id, $council_id, 'mm365_requester_company_council');

      if ($can_access == TRUE):
        apply_filters('mm365_matchrequest_admin_preview', $mr_id, 'publish');
      else:
        echo "Restricted";
      endif;

    }
    ?>


    <!-- Panel ends -->
  </section>

</div>

<?php get_footer();
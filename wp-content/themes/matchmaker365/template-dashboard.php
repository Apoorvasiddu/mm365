<?php
/**
 * Template Name: Dashboard General
 *
 */

$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole', ['mmsdc_manager', 'council_manager', 'business_user', 'super_buyer']);
get_header();

?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>

  </div>
  <div class="dashboard-content-panel">
    <?php
    if (isset($_REQUEST['cid'])):
      get_template_part('template-parts/company', 'view');
    else:
      ?>
      <h2 class="heading-large">
        <?php echo get_the_title(); ?>
      </h2>
      <section class="company_preview">
        <?php
        while (have_posts()):
          the_post();
          the_content();
        endwhile;
        ?>
      </section>
    <?php endif; ?>

  </div>
</div>

<?php
get_footer();
<?php
/**
 * Template Name: Admin - Match Preference
 *
 */
$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole',['council_manager','mmsdc_manager']);

  get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">
  <?php get_template_part( 'template-parts/admin','matchpreference' ); ?>
        <?php
        while(have_posts()) : the_post();
        the_content();
        endwhile; 
        ?>

  </div>
</div>
<?php  get_footer();
<?php
/**
 * Template Name: Request For Match
 *
 */


$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole',['business_user']);

//Check if user has active registration else redirect
apply_filters('mm365_helper_check_companyregistration', 'register-your-company');

//Check for drafted items
$args = array(
  'author' => $user->ID,
  'post_type' => 'mm365_matchrequests',
  'post_status' => 'draft',
  'posts_per_page' => 1,
  'orderby' => 'title',
);
$drafted_items = new WP_Query($args);
while ($drafted_items->have_posts()):
  $drafted_items->the_post();
  if ($drafted_items):
    foreach ($drafted_items as $df_items) {
      if (get_post_status(get_the_ID()) == 'draft') {
        $users_published_company = '';
        $users_company = get_the_ID();
        $redirect = site_url() . '/edit-matchrequest?mr_id=' . get_the_ID() . '&mr_state=draft';

        wp_redirect(add_query_arg('_wpnonce', wp_create_nonce('match_request'), $redirect));
        exit;
      }
    }
  endif;
endwhile;
get_header();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>
  <div class="dashboard-content-panel">

    <?php
    get_template_part('template-parts/matchrequests', 'list');
    ?>

  </div>
</div>
<?php
get_footer();
<?php
/**
 * Template Name: CM - Upcoming Conferences
 **/
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['council_manager', 'business_user']);

$current_user_council_id = $_COOKIE['active_council_id'];
get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>
  <div class="dashboard-content-panel">
    <h1 class="heading-large pbo-10">Upcoming Conferences</h1>
    <div class="conferences">


      <?php
      wp_reset_postdata();
      $args = array(
        'post_type' => 'mm365_conferences',
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_query' => array(
          array(
            'key' => 'conf_date_iso',
            'value' => date("Y-m-d"),
            'compare' => '>=',
            'type' => 'DATE'
          ),
          array(
            'relation' => 'OR',
            array(
              'key' => 'conf_organized_council_id',
              'value' => $current_user_council_id,
              'compare' => '='
            ),
            array(
              'key' => 'conf_scope',
              'value' => 'national',
              'compare' => '='
            ),
          )

        )
      );

      $conferences = new WP_Query($args);
      if ($conferences->have_posts()){
        while ($conferences->have_posts()):
          $conferences->the_post();
          $conf_id = get_the_ID();
          ?>

          <div class="conferences-card">

            <h3>
              <?php echo get_the_title(); ?>
            </h3>
            <p>
              <?php echo get_post_meta(get_the_ID(), 'conf_keywords', true) ?>
            </p>
            <div class="row pbo-10">
              <div class="col-4">Date:
                <?php echo esc_html(get_post_meta($conf_id, 'conf_date', true)); ?>
              </div>
              <div class="col-4 text-center"><span>
                  <?php echo apply_filters('mm365_offline_conference_get_deligates_count', $conf_id) ?> /
                  <?php echo get_post_meta($conf_id, 'conf_maximum_deligates', true); ?> Deligates
                </span></div>
              <div class="col-4 text-right">by
                <?php echo get_post_meta($conf_id, 'conf_organizer', true); ?>
              </div>
            </div>
            <div class="d-flex justify-content-between fl-gp-20">
              <?php
              echo '<span><a href="' . add_query_arg('_wpnonce', wp_create_nonce('view_offline_conf'), site_url('view-offline-conference') . '?conf_id=' . $conf_id) . '"  class="btn btn-primary white">More Details</a></span>';
              $applicationStatus = apply_filters('mm365_offline_conferences_get_application_status', $conf_id, $_COOKIE['active_company_id']);
              if ($applicationStatus != NULL):
                ?>
                <div class="d-flex align-items-center">Participation status:&nbsp;&nbsp;
                  <span class="application-status <?php echo $applicationStatus; ?>">
                    <?php echo $applicationStatus; ?>
                  </span>
                </div>
              <?php endif; ?>
            </div>
          </div>


          <?php
        endwhile;
      }else{
        echo "There are no conferences! Check back later.";


      }
      wp_reset_postdata();



      ?>
    </div>
  </div>
</div>
<?php
get_footer();
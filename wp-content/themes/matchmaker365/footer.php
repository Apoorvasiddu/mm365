<?php

//Replace the default council id after deployment
$user = wp_get_current_user();
if (!empty($user) and in_array('council_manager', (array) $user->roles) or in_array('business_user', (array) $user->roles)) {

  if (!isset($_COOKIE['active_council_id'])) {
    $default_council_id = apply_filters('mm365_helper_get_usercouncil', 1, $user->ID);
  } else {
    $default_council_id = $_COOKIE['active_council_id'];
  }


} else {

  $args = array(
    'post_type' => 'mm365_msdc',
    'post_status' => 'publish',
    's' => 'Michigan Minority Supplier Development Council',
  );

  $footer_query = new WP_Query($args);
  if ($footer_query->have_posts()):
    while ($footer_query->have_posts()):
      $footer_query->the_post();
      $default_council_id = get_the_ID();
    endwhile;
  endif;
}
?>
<footer class="main-footer">
  <div class="container">
    <?php
if ( method_exists( 'user_switching', 'get_old_user' ) ) {
  $old_user = user_switching::get_old_user();

  if ( $old_user ) {

      $link = user_switching::switch_back_url( $old_user );

      $link = add_query_arg( array(
          'redirect_to' => urlencode( get_bloginfo('url') ),
      ), $link );

      printf(
          '<a id="custom_user_switching_switch_on"  href="%1$s">Back to <span>%2$s</span></a>',
          esc_url( $link ),
          esc_html( $old_user->display_name )
      );
  }
}
    ?>
  </div>
  <section class="container">

    <?php
    //List of councils
    $args = array(
      'post_type' => 'mm365_msdc',
      'posts_per_page' => -1,
      'orderby' => 'date',
      'fields' => 'ids',
      'meta_query' => array(
        'relation' => 'OR',
        array(
          'key' => 'mm365_council_hidefromfooter',
          'value' => '1',
          'compare' => '!=',
        ),
        array(
          'key' => 'mm365_council_hidefromfooter',
          'value' => '1',
          'compare' => 'NOT EXISTS',
        ),
      )
    );
    $loop = new WP_Query($args);
    $councils_list = array();
    while ($loop->have_posts()):
      $loop->the_post();
      $councils_list[] = get_the_ID();
    endwhile;
    wp_reset_postdata();


    //Sorting for showing users council as default
    if (($key = array_search($default_council_id, $councils_list)) !== false) {
      unset($councils_list[$key]);
      array_unshift($councils_list, $default_council_id);
    }

    ?>

    <!-- Tabs -->
    <div class="row">
      <div class="col-12">
        <span class="ftrarrow larr"><i class="fas fa-angle-left"></i></span>

        <div class="swipe-tabs">
          <?php foreach ($councils_list as $council_id) { ?>
            <div class="swipe-tab"><span class="speech-bubble">
                <?php echo get_post_meta($council_id, 'mm365_council_shortname', TRUE); ?>
              </span></div>
          <?php } ?>
        </div>

        <span class="ftrarrow rarr"><i class="fas fa-angle-right"></i></span>
      </div>
    </div>
    <!-- Content -->
    <div class="swipe-tabs-container">
      <?php foreach ($councils_list as $council_id) {

        $facebook = get_post_meta($council_id, 'mm365_council_facebook', TRUE);
        $instagram = get_post_meta($council_id, 'mm365_council_instagram', TRUE);
        $twitter = get_post_meta($council_id, 'mm365_council_twitter', TRUE);
        $linkedin = get_post_meta($council_id, 'mm365_council_linkedin', TRUE);
        $youtube = get_post_meta($council_id, 'mm365_council_youtube', TRUE);
        $flickr = get_post_meta($council_id, 'mm365_council_flickr', TRUE);
        $email = get_post_meta($council_id, 'mm365_council_email', TRUE);
        $phone = get_post_meta($council_id, 'mm365_council_phone', TRUE);

        $logo = get_post_meta($council_id, 'mm365_council_logo', TRUE);
        if (!empty($logo)) {
          foreach ($logo as $key => $value) {
            $path_to_file = $value;
          }
        } else {
          $path_to_file = '';
        }

        ?>
        <div class="swipe-tab-content">

          <div class="row">
            <div class="col-md-3  pbo-30 quick-links">
              <img src="<?php echo esc_url($path_to_file); ?>" height="135px" alt="" />
            </div>
            <div class="col-md-3 pbo-30">
              <h4>
                <?php echo get_the_title($council_id); ?>
              </h4>
              <p>
                <?php echo get_post_meta($council_id, 'mm365_council_description', TRUE); ?>
              </p>
            </div>

            <div class="col-md-3  pbo-30">
              <h5>Stay Connected</h5>
              <ul class="social-links">
                <?php if ($facebook != ''): ?>
                  <li><a href="https://facebook.com/<?php echo esc_html($facebook); ?>" target="_blank"><img
                        src="<?php echo get_template_directory_uri() ?>/assets/images/fb.svg" alt=""></a></li>
                <?php endif; ?>
                <?php if ($twitter != ''): ?>
                  <li><a href="https://twitter.com/<?php echo esc_html($twitter); ?>" target="_blank"><img
                        src="<?php echo get_template_directory_uri() ?>/assets/images/twittr.svg" alt=""></a></li>
                <?php endif; ?>
                <?php if ($linkedin != ''): ?>
                  <li><a href="https://www.linkedin.com/<?php echo esc_html($linkedin); ?>" target="_blank"><img
                        src="<?php echo get_template_directory_uri() ?>/assets/images/Linkin.svg" alt=""></a></li>
                <?php endif; ?>
                <?php if ($youtube != ''): ?>
                  <li><a href="https://www.youtube.com/<?php echo esc_html($youtube); ?>" target="_blank"><img
                        src="<?php echo get_template_directory_uri() ?>/assets/images/youtub.svg" alt=""></a></li>
                <?php endif; ?>
                <?php if ($flickr != ''): ?>
                  <li><a href="https://www.flickr.com/<?php echo esc_html($flickr); ?>" target="_blank"><img
                        src="<?php echo get_template_directory_uri() ?>/assets/images/flicker.svg" alt=""></a></li>
                <?php endif; ?>
                <?php if ($instagram != ''): ?>
                  <li><a href="https://instagram.com/<?php echo esc_html($instagram); ?>" target="_blank"><img
                        src="<?php echo get_template_directory_uri() ?>/assets/images/insta.svg" alt=""></a></li>
                <?php endif; ?>
              </ul>
            </div>
            <div class="col-md-3  pbo-30">
              <h5>Contact Us</h5>
              <p>
                <?php
                echo get_post_meta($council_id, 'mm365_council_address', TRUE) . "<br/>";
                echo apply_filters('mm365_helper_get_cityname', get_post_meta($council_id, 'mm365_council_city', TRUE)) . " ";
                echo apply_filters('mm365_helper_get_statename', get_post_meta($council_id, 'mm365_council_state', TRUE));
                echo "<br/>" . get_post_meta($council_id, 'mm365_council_zip', TRUE);
                $map_link = get_post_meta($council_id, 'mm365_council_map_link', TRUE);
                $website = get_post_meta($council_id, 'mm365_council_website', TRUE);
                ?>
              </p>
              <p><i class="fas fa-phone-volume"></i>&nbsp;&nbsp;&nbsp;<a href="tel:<?php echo esc_html($phone); ?>">
                  <?php echo esc_html($phone); ?>
                </a><br />
                <i class="fas fa-envelope"></i>&nbsp;&nbsp;<a href="mailto:<?php echo esc_html($email); ?>">
                  <?php echo esc_html($email); ?>
                </a><br />
                <?php if ($website != ''): ?><i class="fas fa-globe"></i>&nbsp;&nbsp;<a target="_blank"
                    href="<?php echo esc_html($website); ?>">
                    <?php echo esc_html($website); ?>
                  </a><br />
                <?php endif; ?>
                <?php if ($map_link != ''): ?><i class="fas fa-map-marker-alt"></i>&nbsp;&nbsp;<a target="_blank"
                    href="<?php echo esc_html($map_link); ?>">Map</a>
                <?php endif; ?>
              </p>
            </div>
          </div>

        </div>
      <?php } ?>

    </div>

  </section>
  <?php

  wp_footer();
  ?>
</footer>
</body>

</html>
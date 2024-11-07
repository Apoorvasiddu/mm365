<?php
/**
 * Template Name: Choose Match Request Type
 *
 */


$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['business_user']);

//Check if user has active registration else redirect
do_action('mm365_helper_check_companyregistration', 'register-your-company');



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

            <!-- <h1 class="heading-large pbo-10">Find the right supplier for your requirement</h1> -->

            <!-- Choice -->

            <section class="company_preview">

                  <div class="mr-search-types">
                        <div class="btns-block">
                              <h1 class="heading-large pbo-10 uppercase">Request For Match</h1>
                              <p>Matchmaker365 facilitates the identification of suitable suppliers to meet your
                                    business needs. Utilize the <strong>Quick Search</strong> feature for efficient identification and
                                    connection with preferred suppliers, or leverage the <strong>Detailed Search</strong> option to
                                    pinpoint the ideal supplier from our extensive database. Our advanced search
                                    capabilities include filters such as NAICS codes, certifications, and company size,
                                    enabling precise supplier selection tailored to your requirements. </p>
                              <div>
                                    <a href="<?php echo get_site_url()?>/matchrequest-quick-search" class="btn btn-primary">

                                          <svg fill="currentColor" width="42px" height="42px" viewBox="0 0 512 512"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <title>search</title>
                                                <path
                                                      d="M416 448L319 351Q277 383 224 383 181 383 144 362 107 340 86 303 64 266 64 223 64 180 86 143 107 106 144 85 181 63 224 63 267 63 304 85 341 106 363 143 384 180 384 223 384 277 351 319L448 416 416 448ZM223 336Q270 336 303 303 335 270 335 224 335 177 303 145 270 112 223 112 177 112 144 145 111 177 111 224 111 270 144 303 177 336 223 336Z" />
                                          </svg>


                                          Quick Search</a>
                                    <a href="<?php echo get_site_url()?>/new-request-for-match" class="btn btn-primary">
                                          <svg fill="currentColor" width="42px" height="42px" viewBox="0 0 1920 1920"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                      d="M262.749 410.667H.000648499V282.667H262.749C292.139 145.504 414.06 42.6667 560 42.6667 705.94 42.6667 827.861 145.504 857.251 282.667H1920V410.667H857.251C827.861 547.829 705.94 650.667 560 650.667 414.06 650.667 292.139 547.829 262.749 410.667ZM384 346.667C384 249.465 462.798 170.667 560 170.667 657.202 170.667 736 249.465 736 346.667 736 443.869 657.202 522.667 560 522.667 462.798 522.667 384 443.869 384 346.667ZM.000648499 896H1009.42C1038.81 758.837 1160.73 656 1306.67 656 1452.61 656 1574.53 758.837 1603.92 896H1920V1024H1603.92C1574.53 1161.16 1452.61 1264 1306.67 1264 1160.73 1264 1038.81 1161.16 1009.42 1024H.000648499V896ZM1306.67 784C1209.46 784 1130.67 862.798 1130.67 960 1130.67 1057.2 1209.46 1136 1306.67 1136 1403.87 1136 1482.67 1057.2 1482.67 960 1482.67 862.798 1403.87 784 1306.67 784ZM857.251 1637.33C827.861 1774.5 705.94 1877.33 560 1877.33 414.06 1877.33 292.139 1774.5 262.749 1637.33H.000648499V1509.33H262.749C292.139 1372.17 414.06 1269.33 560 1269.33 705.94 1269.33 827.861 1372.17 857.251 1509.33H1920V1637.33H857.251ZM384 1573.33C384 1476.13 462.798 1397.33 560 1397.33 657.202 1397.33 736 1476.13 736 1573.33 736 1670.54 657.202 1749.33 560 1749.33 462.798 1749.33 384 1670.54 384 1573.33Z" />
                                          </svg>
                                          Detailed Search</a>
                              </div>
                        </div>
                        <div class="image-block">
                              <img src="<?php echo get_template_directory_uri(); ?>/assets/images/collaboration.svg"
                                    alt="">
                        </div>

                  </div>


            </section>


      </div>
</div>

<?php
get_footer();
<?php
/**
 * Template Name: Match Request Quick Search
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
                        <div class="btns-block text-center">
                              <h1 class="heading-large pbo-10 uppercase text-center">Quick Search</h1>
                              <p>If you know the supplier name, quick search helps you to find them and connect with
                                    them with ease</p>

                              <form method="post" enctype="multipart/form-data" id="mm365_quick_match"
                                    class="quick_match_form" data-parsley-validate>
                                    <input type="text" name="company_name" id="company_name" required>
                                    <button id="quick_match" type="submit" class="btn btn-primary invert">Find
                                          Company</button>
                              </form>

                        </div>

                  </div>

                                    <!-- Ajax Push data here -->
                                    <div id="quickmatch-data-table" class="pto-30">

            </section>

            


                        <!-- Convert selection to match request -->
                        <section id="mm365-select-matches-quicksearch">
                              <div class="container">
                                    <div class="row">
                                          <div class="col-6"> <span id="mm365-selected-matches"></span> <span
                                                      id="mm365-selected-matches-message">companies</span> selected
                                          </div>
                                          <div class="col-6 text-right">
                                                <button id="qs-convert-to-match"
                                                data-redirect="<?php echo site_url()?>/view-match/?mr_id="
                                                data-userid="<?php echo esc_html($user->ID); ?>"
                                                data-requester_company_id="<?php echo esc_html($_COOKIE['active_company_id']); ?>"
                                                data-requester_council_id="<?php echo esc_html(get_post_meta($_COOKIE['active_company_id'], 'mm365_company_council', true)); ?>"
                                                class="btn btn-primary approve-matched-companies">Make Match Request</button>
                                          </div>
                                    </div>
                              </div>
                        </section>

                  </div>


      </div>
</div>

<?php
get_footer();
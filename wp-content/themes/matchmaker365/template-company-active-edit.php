<?php
/**
 * Template Name: Active Company Edit
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['business_user']);

get_header();

//Get company info to edit
$company_id = $_REQUEST['cid'];
$args = array(
  'p' => $company_id,
  'post_type' => 'mm365_companies',
  'post_status' => 'publish',
  'posts_per_page' => -1,
  'orderby' => 'title',
  'author' => $user->ID
);

$loop = new WP_Query($args);
if ($loop->have_posts()):
  while ($loop->have_posts()):
    $loop->the_post();
    //Loop adjustment
    $cmp_id = get_the_ID();

    $stype = get_post_meta($cmp_id, 'mm365_service_type', true);
    ?>
    <div class="dashboard">
      <div class="dashboard-navigation-panel">
        <!-- Users Menu -->
        <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
      </div>

      <div class="dashboard-content-panel">
        <div class="container-fluid">
          <div class="row ">
            <div class="col-12">

              <h1 class="heading-large pbo-10">Edit Company Information</h1>
              <div id="ajax-warnings"></div>

              <form method="post" id="active_update_company" action="#" data-parsley-validate enctype="multipart/form-data">

                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
                <input type="hidden" name="submitting_user_id" value="<?php echo $user->ID; ?>">
                <input type="hidden" name="company_status" value="active">

                <section class="company_preview">
                  <?php
                  $args = array('cmp_id' => $cmp_id);
                  get_template_part('template-parts/company', 'edit', $args); 
                  ?>

                  <div class="form-row mto-30">
                    <div class="col text-right">
                      <a class="btn btn-danger" href="<?php echo site_url('/register-your-company/'); ?>">
                      <?php _e('Cancel', 'mm365') ?>
                      </a>
                      <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                      <input type="hidden" name="submitted" id="submitted" value="true" />
                      <button id="comp_submit" type="submit" class="btn btn-primary">
                        <?php _e('Update', 'mm365') ?>
                      </button>
                    </div>
                  </div>

                </section>
              </form>

            </div>
          </div>

          <!-- Panel ends -->
        </div>
      </div>

      <?php

  endwhile;
else:
  ?>
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="card text-center pto-100 pbo-100">
            <img class="card-img-top" height="120px"
              src="<?php echo get_template_directory_uri() ?>/assets/images/failure.svg" alt="Card image cap">
            <div class="card-body">
              <h1 class="card-title">Unauthorised action!</h1>
              <p class="card-text"></p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Panel ends -->
    <?php
endif;
wp_reset_postdata();

?>
</div>
<?php
get_footer();
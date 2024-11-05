<?php
/**
 * Template Name: Company Submit
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['business_user']);

                //Clear cookie
                unset($_COOKIE['active_company_id']);
                unset($_COOKIE['active_company_name']);
                unset($_COOKIE['active_council_id']);
                setcookie('active_company_id', '', -1, '/');
                setcookie('active_company_name', '', -1, '/');
                setcookie('active_council_id', '', -1, '/');

get_header();
?>
<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>
  <section class="dashboard-content-panel">
    <!-- Panel starts -->
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="card text-center pto-100 pbo-100">
            <?php
            $company_id = $_REQUEST['cid'];
            //Check if post belongs to current user
            if (get_current_user_id() == get_post_field('post_author', $company_id)) {
              $company_post = array('ID' => $company_id, 'post_status' => 'publish');
              if (wp_update_post($company_post)) {
                $icon = 'success';
                $message = "Thanks for your submission!";
                $sub_text = "Your company profile is successfully registered with Match Maker 365.";


              } else {
                $icon = 'failure';
                $message = "Oops! Something went wrong";
                $sub_text = "This could be a temporary glicth in the system. Please try again later";
              }

            } else {
              $icon = 'failure';
              $message = "Unauthorised action!";
              $sub_text = "";
            }
            ?>
            <img class="card-img-top" height="120px"
              src="<?php echo get_template_directory_uri() ?>/assets/images/<?php echo esc_html($icon); ?>.svg"
              alt="Card image cap">
            <div class="card-body">
              <h1 class="card-title">
                <?php echo esc_html($message); ?>
              </h1>
              <p class="card-text">
                <?php echo esc_html($sub_text); ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Panel ends -->
  </section>

</div>

<?php
get_footer();
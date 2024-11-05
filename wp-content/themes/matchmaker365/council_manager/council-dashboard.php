<?php
/**
 * Template Name: Council Manager - Dashboard
 *
 */
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['council_manager']);

get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">
        <section class="row admin-dash-filter">
            <div class="col-6"><h2 class="heading-large">Dashboard - <?php echo esc_html($mm365_helper->council_info($council_id)); ?></h2></div>
            <div class="col-6 text-right">
            <div class="select">
                <select name="slct" id="mm365_card_filter_select">
                    <option selected value="week">Last one week</option>
                    <option value="month">Last one month</option>
                    <option value="year">Last one year</option>
                </select>
            </div>
        </section>

        <section class="report-cards pbo-30 pto-30">
        <!-- CARDS STARTS HERE -->


        <!-- CARDS ENDS HERE -->
        </section>
  </div>
</div>
<?php  get_footer();
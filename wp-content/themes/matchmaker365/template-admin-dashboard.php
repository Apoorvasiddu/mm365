<?php
/**
 * Template Name: Admin Dashboard
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['mmsdc_manager', 'council_manager']);

//Restrict data for council manager
if (in_array('council_manager', (array) $user->roles)) {
    $council_id = apply_filters('mm365_helper_get_usercouncil', $user->ID);
} else {
    $council_id = NULL;
}

get_header();

?>

<div class="dashboard">
    <div class="dashboard-navigation-panel">
        <!-- Users Menu -->
        <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
    </div>
    <div class="dashboard-content-panel">
        <section class="row admin-dash-filter">
            <div class="col-4">
                <h2 class="heading-large text-left">
                    <?php esc_html_e('Dashboard', 'mm365'); ?>
                </h2>
            </div>
            <div class="col-8 d-flex justify-content-end gap-2-per">

                <?php if ($council_id == ''): ?>
                    <!-- Council filter -->
                    <div class="council-filter" data-intro="Filter to show statistics from a specific council">
                        <div class="select">
                            <select id="dash-councilFilter">
                                <option value="">
                                    <?php esc_html_e('All Councils', 'mm365'); ?>
                                </option>
                                <?php
                                apply_filters('mm365_dropdown_councils', NULL);
                                ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="select"
                    data-intro="You can filter the statistics for last one week, last one month or last one year from current date">
                    <select name="slct" id="mm365_card_filter_select"
                        data-council_id="<?php echo esc_html($council_id); ?>">
                        <option selected value="week">
                            <?php esc_html_e('Last one week', 'mm365'); ?>
                        </option>
                        <option value="month">
                            <?php esc_html_e('Last one month', 'mm365'); ?>
                        </option>
                        <option value="year">
                            <?php esc_html_e('Last one year', 'mm365'); ?>
                        </option>
                    </select>
                </div>
            </div>
        </section>

        <section class="report-cards pbo-30 pto-10"
            data-intro="You can click download icon to download the detailed result set in excel format or  click the 'view icon' to see the reports on screen">
            <!-- CARDS STARTS HERE -->
            <?php
            echo apply_filters('mm365_admin_dashboard_status_cards', 'week', $council_id)
                ?>
            <!-- CARDS ENDS HERE -->
        </section>

    </div>
</div>
<?php get_footer();
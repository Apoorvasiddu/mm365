<?php
/**
 * Template Name: Company Registartion
 *
 */
$user = wp_get_current_user();

$companies_list = apply_filters('mm365_businessuser_companies_list', $user->ID);


//Check if active company is in draft state
if (isset($_COOKIE['active_company_id'])) {

        $active_company_id = $_COOKIE['active_company_id'];

        //Check if user hasn't completed company registartion, then redirect
        if (get_post_status($active_company_id) == 'draft') {
                $users_published_company = '';
                $users_company = $active_company_id;
                $redirect = site_url() . '/edit-company/?base=' . site_url() . '&cid=' . $active_company_id . '&rdi=' . rand();
                wp_redirect($redirect);
                exit;
        }

        $users_published_company = $active_company_id;
        $users_company = $active_company_id;

} else {
        if (count($companies_list) > 0) {

            do_action('mm365_helper_check_companyregistration', 1, 'register-your-company');
        }
        $users_published_company = '';
        $users_company = '';
}


get_header();

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
                                        <?php
                                        if ($users_published_company != '') {

                                                echo '<h1 class="heading-large pbo-10">Company Information</h1>';

                                                $company_type = get_post_meta($users_published_company, 'mm365_service_type', true);

                                                if ($company_type == 'seller') {

                                                        $certificate_status = get_post_meta($users_published_company, 'mm365_certification_status', true);
                                                        //If not uploaded
                                                        if (empty($certificate_status)) {
                                                                echo '<div class="alert alert-primary" role="alert">If you have a valid MBE certificate, please remember to upload it using the menu option <a href="' . site_url('/certificate-upload') . '" class="font-weight-bold">“Upload Certificates”</a></div>';
                                                        }

                                                        //If expired
                                                        if ($certificate_status == 'expired') {
                                                                echo '<div class="alert alert-primary" role="alert">Your MBE certificate is expired. Please renew your certificate and upload it using the menu option <a href="' . site_url('/certificate-upload') . '" class="font-weight-bold">“Upload Certificates”</a></div>';
                                                        }
                                                }

                                                apply_filters('mm365_company_show', $users_published_company, 'publish', false);

                                        } else {
                                                ?>
                                                <h1 class="heading-large pbo-10">Register your company</h1>
                                                <div id="ajax-warnings"></div>
                                                <form method="post" id="reg_company" action="#" data-parsley-validate
                                                        enctype="multipart/form-data">
                                                        <section class="company_preview">
                                                                <?php
                                                                get_template_part('template-parts/company', 'register');
                                                                ?>
                                                        </section>

                                                        <div class="form-row mto-30">
                                                                <div class="col-lg-12 text-right">
                                                                        <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                                                                        <input type="hidden" name="submitted" id="submitted"
                                                                                value="true" />
                                                                        <button id="comp_submit" type="submit"
                                                                                class="btn btn-primary"
                                                                                onclick="tinyMCE.triggerSave(true,true);">
                                                                                <?php _e('Save & Preview', 'mm365') ?>
                                                                        </button>
                                                                </div>
                                                        </div>

                                                </form>

                                        </div>
                                </div>
                        <?php } ?>

                        <!-- Panel ends -->
                </div>
        </div>
</div>
</div>
</div>
<?php
get_footer();
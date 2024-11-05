<?php
/**
 * 
 * Template Name: Register New Company
 *
 * Users with existing company registering a new company
 */
$user = wp_get_current_user();
$user_id = $user->ID;

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

                                        <h1 class="heading-large pbo-10">Register new company</h1>
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
                                </div>
                        </div>


                        </form>
                        <!-- Panel ends -->
                </div>

        </div>

</div>
</div>
</div>
<?php
get_footer();
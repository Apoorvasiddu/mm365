<?php
/**
 * Template Name: Certificate Upload
 *
 */

$user = wp_get_current_user();

//ATTENTION HERE
do_action('mm365_helper_check_loginandrole', ['business_user']);

//Check if user has active registration else redirect
do_action('mm365_helper_check_companyregistration', 'register-your-company');

get_header();
?>

<div class="dashboard">
    <div class="dashboard-navigation-panel">
        <!-- Users Menu -->
        <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
    </div>
    <div class="dashboard-content-panel">

        <h1 class="heading-large pbo-10">
            Upload Certificate
        </h1>
        <section class="company_preview">
            <form method="post" id="mm365_upload_certificate" action="#" data-parsley-validate
                enctype="multipart/form-data">
                <div class="form-row form-group"
                    data-intro="Drag & drop your MBE Certificate, scanned in PDF/PNG format">
                    <div class="col">
                        <label for="">Upload certificate<span>*</span>
                            <br /><small>Drag & drop your MBE Certificate
                                (You can only upload .jpg, .png or .pdf formats. File size should not exceed 2MB)
                            </small>
                        </label>
                        <br />
                        <div class="dropzonee" id="certificate-dropzone" data-existing="">
                            <div class="dz-message needsclick" for="files">Drag & drop your MBE Certificate or click to
                                upload.<br />
                                <small>(You can only upload .jpg, .png or .pdf formats. File size should not exceed
                                    2MB)</small>
                                <div class="fallback">
                                    <input class="form-control-file" type="file" id="wp_custom_attachment" name="files"
                                        multiple />
                                </div>
                            </div>
                        </div>
                        <ul class="parsley-errors-list filled" id="validate-capability-statement" aria-hidden="false">
                            <li class="parsley-required capability-statemets-error">This value is required.</li>
                        </ul>
                    </div>
                </div>

                <div class="form-row form-group">
                    <div class="col-md-3" data-intro="Select the expiry date of the certificate">
                        <label for="">Date of expiry <span>*</span></label>
                        <input type="text" name="expiry_date" id=""
                            class="certificate_expiry_date form-control flatpickr-input active" required=""
                            placeholder="" data-parsley-errors-container=".first_choice_error" readonly="readonly">
                        <div class="first_choice_error"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-12 text-right">
                        <input type="hidden" name="uploaded_company_id" id="uploaded_company_id"
                            value="<?php echo esc_attr($_COOKIE['active_company_id']); ?>">
                        <input type="hidden" name="company_council_id" id="company_council_id"
                            value="<?php echo esc_html(get_post_meta($_COOKIE['active_company_id'], 'mm365_company_council', true)); ?>" />
                        <button id="certificate_upload" type="submit" class="btn btn-primary"
                            data-intro="Submit for admin approval" data-position="left">
                            <?php esc_html_e('Submit', 'mm365') ?>
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <?php get_template_part('certificates/certificates', 'list'); ?>

    </div>
</div>
</div>

<?php
get_footer();
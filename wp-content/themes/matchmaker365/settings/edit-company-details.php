<?php
/**
 * Template Name: Edit Company Description
 *
 */


if (!wp_verify_nonce( $_REQUEST['_wpnonce'], 'edit_company_description' )) {
  wp_safe_redirect(site_url());
  die();
}

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

get_header();

$company_id = $_REQUEST['cmp_id'];
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
  </div>
  <div class="dashboard-content-panel">

    <h1 class="heading-large pbo-10">Edit Company Description - <?php echo get_the_title(  $company_id ) ?></h1>

    <section class="company_preview">

    <form method="post" id="mm365_update_company_description" action="#"  data-parsley-validate enctype="multipart/form-data" >
                    
        <textarea id="edit_company_description" name="company_description"  rows="10">
         <?php echo get_post_meta( $company_id, 'mm365_company_description', true ); ?>
        </textarea>
        <div class="form-row pto-30">
            <div class="col text-right">
                <input type="hidden" id="company_id" name="company_id" value="<?php echo esc_html($company_id);?>">
                <input type="hidden" id="after_success_redirect" name="redirect_to" value="<?php echo esc_html(site_url('view-company?cid='.$company_id));?>">
                <button type="submit" class="btn btn-primary" ><?php _e('Update Company Description', 'mm365') ?></button>
            </div>
         </div>

      </form>
    </section>

  </div>
</div>  
  <?php
get_footer();

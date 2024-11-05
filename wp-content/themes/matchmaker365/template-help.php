<?php
/**
 * Template Name: Help Page
 *
 */

$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole', ['mmsdc_manager', 'council_manager', 'business_user']);

get_header();

require_once('wp-load.php');
wp_enqueue_media();

?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>

  </div>
  <div class="dashboard-content-panel">
    <?php
    if (isset($_REQUEST['cid'])):
      get_template_part('template-parts/company', 'view');
    endif;
    ?>
    <h2 class="heading-large">
      <?php the_title(); ?>
    </h2>
    <section class="company_preview">
      <?php
      if (isset($_REQUEST['mode']) and $_REQUEST['mode'] == 'edit') {
        ?>
        <form method="post" id="update_help_docs" action="#" data-parsley-validate enctype="multipart/form-data">
          <textarea id="help_desc_blocks" rows="20" name="help_contents" data-parsley-errors-container=".descError">
            <?php
            while (have_posts()):
              the_post();
              the_content();
            endwhile;
            ?>
       </textarea>

          <div class="form-row mto-30">
            <div class="col text-right">
              <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
              <input type="hidden" name="page_id" value="<?php echo $post->ID; ?>">
              <button id="help_update" type="submit" class="btn btn-primary">
                <?php _e('Update', 'mm365') ?>
              </button>
            </div>
          </div>
        </form>
        <?php
      } else {
        while (have_posts()):
          the_post();
          the_content();
        endwhile;
        ?>
        <?php if (in_array('mmsdc_manager', (array) $user->roles)) { ?>
          <div class="row mto-30">
            <div class="col-lg-12 text-right"><a data-intro="Edit the details of your company" data-step="2"
                href="<?php echo site_url($post->post_name) ?>?mode=edit" class="btn btn-primary">Edit</a> </div>
            <div></div>
          </div>
        <?php } ?>
        <?php
      }
      ?>
    </section>
  </div>
</div>

<?php
get_footer();
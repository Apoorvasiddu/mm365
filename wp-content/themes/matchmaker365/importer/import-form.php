<?php
/**
 * Template Name: Importer - Form
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

get_header();
//$importer = new  Matchmaker_365_Import();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">
    <h2 class="heading-large pbo-20">Import Companies</h2>
    <section class="company_preview">

    <!-- Form Here -->
    <?php //$importer->matchmaker_365_import_ui(); 
    apply_filters('mm365_dataimport_form',1);
    ?>
    <!-- to here -->

    </section>
  </div>
</div>

<?php
get_footer();
<?php
/**
 * Template Name: Super Admin - Manage Buyer
 *
 */

  $user = wp_get_current_user();

  do_action('mm365_helper_check_loginandrole',['mmsdc_manager']);

  get_header();


?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">


    <section class="row admin-dash-filter">
        <div class="col-12"><h3 class="heading-large">Block Buyer</h3></div>
    </section>

    <!-- Search and list buyers  -->
    <section class="company_preview">

    <form method="post" id="mm365_find_buyer" action="#"  data-parsley-validate enctype="multipart/form-data" >
        <div class="form-row form-group" data-intro="To block a buyer, search and find the buyer company name and click 'BLOCK' link next to the status. Blocked buyers cannot login to the platform">
            <div class="offset-lg-2 col-lg-8 d-flex flex-column flex-sm-row gap-15">
                    <label for="">Find Buyer Company</label>
                    <input placeholder="" class="form-control"  type="text" required name="search_buyer" minlength="3" > 
                    <button id="search_buyer" type="submit" class="btn btn-primary" ><?php _e('Search', 'mm365') ?></button>
            </div>    
        </div>
    </form>

    <div id="buyer-data-table" class="pto-30">
    
    </div>

    </section>


  </div>
</div>

<?php
get_footer();
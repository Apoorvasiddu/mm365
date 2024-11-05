<?php
/**
 * Template Name: CM - View Conference
 **/
$conf_id = $_REQUEST['conf_id'];
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['business_user','council_manager']);


//council manager class
if(!empty($_REQUEST['print_conf']) AND is_numeric($_REQUEST['print_conf'])){
  do_action('mm365_offline_conferences_export_applicants_list',$conf_id);
}

get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>
  <div class="dashboard-content-panel">
    <div class="row">
      <div class="col-md-4">
        <h1 class="heading-large pbo-10">
          <a href="#" onclick="history.back();"><img class="back-arrow"
              src="<?php echo get_template_directory_uri() ?>/assets/images/arrow-left.svg" height="36px" alt=""></a>
          View Conference
        </h1>
      </div>
      <div class="col-md-8  text-right">

        <?php

        //Check if the application count has exceeded the limit
        $existing_registartion_count = apply_filters('mm365_offline_conference_get_deligates_count',$conf_id);

        //Apply only for supplier
        if (in_array('business_user', (array) $user->roles)) {


          $max_deligates_allowed = get_post_meta($conf_id, 'conf_maximum_deligates', true);

          //Registration closing data 
          $reg_closing_date = get_post_meta($conf_id, 'conf_registration_closing_date', true);

          $date1 = new DateTime("today");
          $date2 = new DateTime($reg_closing_date);


          //Check if the current user has already applied
          $existing_supplier_registration = get_post_meta($conf_id, 'conf_applied_suppliers');
          if (!in_array($_COOKIE['active_company_id'], $existing_supplier_registration)) {
            //Show only if deligate count is not exceeded
            if (($existing_registartion_count < $max_deligates_allowed) and ($date2 > $date1)) {
              ?>
              <a id="applyConferenceParticipation" href="#" class="btn btn-primary green">Apply Particiaption</a>
              <?php
            }

          } else {
            $applicationStatus = apply_filters('mm365_offline_conferences_get_application_status',$conf_id, $_COOKIE['active_company_id']);
            ?>
            Participation status:&nbsp;&nbsp;<span class="application-status <?php echo $applicationStatus; ?>">
              <?php echo $applicationStatus; ?>
            </span>
            <?php
          }

        }

        //Apply only for council managers - Button
        if (in_array('council_manager', (array) $user->roles) AND $existing_registartion_count > 0) {
          get_template_part('conference_module/councilmanager', 'actions', array('conf_id' => $conf_id));
        }

        ?>

      </div>
    </div>
    <!-- Preview Block -->
    <section class="company_preview">
      <?php
      apply_filters('mm365_offline_conferences_show',$conf_id, FALSE);
      ?>
    </section>


    <?php
    if (in_array('council_manager', (array) $user->roles) AND $existing_registartion_count > 0) {
      //tempate part here
      get_template_part('conference_module/applications', 'received', array('conf_id' => $conf_id));
    }
    ?>


  </div><!-- dash panel -->
</div><!--dash -->

<!-- Popup form -->
<div id="applyConferenceParticiaptionForm" class="conferences-apply_popup" style="display:none">
  <form method="post" id="mm365_apply_offline_conf_particiaption" action="#" data-parsley-validate
    enctype="multipart/form-data">
    <h4>Apply for Participation</h4>
    <div class="form-row form-group pto-20">
      <div class="col-4">
        <label for="">Deligate Name<span>*</span></label>
        <input type="text" name="deligate_a_name" required id="" class=" form-control " required placeholder="Full name"
          data-parsley-errors-container=".first_choice_error"
          value="<?php echo get_post_meta($_COOKIE['active_company_id'], 'mm365_contact_person', true); ?>">
      </div>
      <div class="col-4">
        <label for="">Phone<span>*</span></label>
        <input type="text" name="deligate_a_phone" required id="" pattern="[0-9+()\s]+" data-parsley-length="[6, 15]"
          class=" form-control " required placeholder="Phone" data-parsley-errors-container=".first_choice_error"
          value="<?php echo get_post_meta($_COOKIE['active_company_id'], 'mm365_company_phone', true); ?>">
      </div>
      <div class="col-4">
        <label for="">Designation<span>*</span></label>
        <input type="text" name="deligate_a_designation" required id="" class=" form-control " required
          placeholder="Designation" data-parsley-errors-container=".first_choice_error">
      </div>
    </div>
    <div class="form-row">
      <div class="col-12">
        <h6>Accompaniying Deligate (optional)</h6>
      </div>
    </div>
    <div class="form-row form-group">
      <div class="col-4">
        <label for="">Deligate Name</label>
        <input type="text" name="deligate_b_name" id="" class=" form-control " placeholder="Full name"
          data-parsley-errors-container=".first_choice_error"
          value="<?php echo get_post_meta($_COOKIE['active_company_id'], 'mm365_alt_contact_person', true); ?>">
      </div>
      <div class="col-4">
        <label for="">Phone</label>
        <input type="text" pattern="[0-9+()\s]+" data-parsley-length="[6, 15]" name="deligate_b_phone" id=""
          class=" form-control " placeholder="Phone" data-parsley-errors-container=".first_choice_error"
          value="<?php echo get_post_meta($_COOKIE['active_company_id'], 'mm365_alt_phone', true); ?>">
      </div>
      <div class="col-4">
        <label for="">Designation</label>
        <input type="text" name="deligate_b_designation" id="" class=" form-control " placeholder="Designation"
          data-parsley-errors-container=".first_choice_error">
      </div>
    </div>

    <div class="form-row form-group   pto-10">
      <div class="col-12">
        <label for="">Covering Letter<span>*</span></label>
        <textarea name="covering_letter" class=" form-control " required></textarea>
      </div>
    </div>

    <div class="form-row form-group">
      <div class="col-12 text-right">
        <input type="hidden" name="conf_id" value="<?php echo esc_html($conf_id); ?>">
        <input type="hidden" name="supplier_id" value="<?php echo esc_html($_COOKIE['active_company_id']); ?>">
        <button id="applyConfParticiaption" type="submit" class="btn btn-primary">
          <?php _e('Apply', 'mm365') ?>
        </button>
      </div>
    </div>
  </form>
</div>



<?php
get_footer();
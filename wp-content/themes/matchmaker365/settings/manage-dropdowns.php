<?php
/**
 * Template Name: SA - Manage Dropdowns
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

  <h1 class="heading-large pbo-10">Manage Dropdowns</h1>
<!-- Edit Block -->

<section class="company_preview">

<div class="form-row form-group">
  <div class="col-lg-3" data-intro="Add or hide items in various dropdown fields used in the forms">
<!-- Form Items here -->
<label for="">Select the 'dropdown' which you want to edit</label>
    <select name="choose_dropdown_to_edit"  id="choose_dropdown_to_edit" class="choose_dropdown_to_edit form-control mm365-single">
      <option value="">-Select-</option>
      <option value="code-industries">Industries</option>
      <option value="code-services">Services</option>
      <option value="code-certifications">Certifications</option>
      <option value="code-minoritycodes">Minority Category List</option>
      <option value="code-meetingtypes">Meeting Types</option>
      <option value="code-intassi">International Assistance</option>
      <option value="code-closure_completed">Reasons for closure - Completed</option>
      <option value="code-closure_cancellend">Reasons for closure - Cancelled</option>
      <option value="code-subscription_levels">Subscription Levels</option>
    </select>

 </div>
</div>


<!-- Fields data display -->
<div id="manage-dropdowns-fields" class="form-row form-group">



</div>
<a id="endoflist"></a>

<!-- Form Items ends here -->
</section>



  </div><!-- dash panel -->
</div><!--dash -->

<?php
get_footer();
<?php
/**
 * Template Name: SA - Subscription Report
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

<h1 class="heading-large pbo-10">Subscription Report</h1>
<!-- Request for match form -->
<form method="post" id="mm365_subscription_report" action="#"  data-parsley-validate enctype="multipart/form-data" >
  <section class="company_preview">
        <div class="form-row form-group">
                <div class="col-lg-4" data-intro="Council which suppliers belongs to">
                        <label for="">Council<span>*</span></label>
                        <select name="council" id="council" required class="form-control">
                        <option value="">-Select-</option>
                        <?php 
                        apply_filters('mm365_dropdown_councils', null);
                        ?>
                        </select>
                </div>  
                <div class="col-lg-2"> 
                  <label for="">Company Type<span>*</span></label><br/>
                  <input type="radio" name="service_type"  value="buyer"> Buyer
                  &nbsp;<input type="radio" name="service_type"  value="seller" checked> Supplier
                </div>
                <div class="col-lg-2"  >
                    <label for="">Subscription Type</label>
                    <select name="subscription_type" id="subscription_type"  class="form-control">
                      <option value="">Any</option>
                      <?php 
                        apply_filters('mm365_dropdown_subscriptionlevels', array());
                    ?>
                    </select>
                </div>
                <div class="col-lg-4"> 
                  <label for="">Subscription Status<span>*</span></label><br/>
                  <input type="radio" name="subscription_status"  checked value=""> Any
                  &nbsp;&nbsp;<input type="radio" name="subscription_status"  value="Active" > Active
                  &nbsp;&nbsp;<input type="radio" name="subscription_status"  value="Expired" > Expired
                  &nbsp;&nbsp;<input type="radio" name="subscription_status"  value="Not Subscribed"> Not Subscribed
                </div>    
        </div>

        <div id="subscription_date_between_block">
          <div class="row">
                  <div class="col-lg-12"  data-intro="Select the date range.">
                    <label>Subscription expiry date between</label>
                  </div>
          </div>         

          <div class="form-row form-group">
                  <div class="col-lg-2"  data-intro="Select the date range.">
                    <label for="from_date">From</label>
                    <input class="form-control from_date_tdy" type="text"  name="from_date" data-parsley-errors-container=".frmdateError"> 
                    <div class="frmdateError"></div>     
                  </div>
                  <div class="col-12 d-block d-sm-none pbo-30"></div>
                    <div class="col-lg-2"  data-intro="">
                    <label>&nbsp;</label><label for="to_date">To</label>
                          <input id="secondRangeInputTDY" class="form-control to_date_tdy" type="text"  name="to_date" data-parsley-errors-container=".todateError">  
                          <div class="todateError"></div>     
                  </div>
          </div>
        </div>


        <div class="form-row pto-10">
          <div class="col">
                <?php wp_nonce_field( 'post_nonce', 'post_nonce_field' ); ?>
                <input type="hidden" name="current_user" id="current_user" value="<?php echo  esc_html($user->ID); ?>" />
                <button id="subscription_report_generate" type="submit" class="btn btn-primary"  data-redirect="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'subscription_report' ), site_url().'/admin-view-report-subscription' ) ?>" ><?php _e('Submit', 'mm365') ?></button>
           </div>
        </div>
  </section>
</form>

<?php //$subscrptionClass->subscription_report(); ?>


  </div><!-- dash panel -->
</div><!--dash -->

<?php
get_footer();
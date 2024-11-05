<?php
/**
 * Template Name: SA - Update Subscription
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

<h1 class="heading-large pbo-10">Update Subscriptions</h1>
<!-- Request for match form -->
<form method="post" id="mm365_update_subscription" action="#"  data-parsley-validate enctype="multipart/form-data" >
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
               
        </div>

        <div class="form-row form-group">
            <div class="col-lg-8" data-intro="List of Suppliers">
                <label for="">Select Companies<span>*</span></label>
                <select name="suppliers_to_subscribe[]" id="suppliers_to_subscribe" multiple required class="form-control"></select>
            </div>   
      </div>

        <div class="form-row form-group">
               <div class="col-lg-2"  >
                    <label for="">Subscription Type<span>*</span></label>
                    <select name="subscription_type" id="subscription_type" required class="form-control">
                    <?php 
                        apply_filters('mm365_dropdown_subscriptionlevels', array());
                    ?>
                    </select>
                </div>  
                <div class="col-lg-2"  data-intro="Select the date range.">
                  <label for="from_date">Start date<span>*</span></label>
                  <input class="form-control from_date_tdy" type="text" required  name="from_date" data-parsley-errors-container=".frmdateError"> 
                  <span class="calendar-icon"></span>   
                  <div class="frmdateError"></div>     
                </div>
                <div class="col-12 d-block d-sm-none pbo-30"></div>
                   <div class="col-lg-2"  data-intro="The date values are checked based on the company registration date. The maximum duration between From date and To date is one year">
                        <label for="to_date">End date<span>*</span></label>
                        <input id="secondRangeInputTDY" class="form-control to_date_tdy" type="text"  required name="to_date" data-parsley-errors-container=".todateError">  
                        <div class="todateError"></div>     
                </div>
      </div>



        <div class="form-row pto-10">
          <div class="col">
                <?php wp_nonce_field( 'post_nonce', 'post_nonce_field' ); ?>
                <input type="hidden" name="current_user" id="current_user" value="<?php echo  esc_html($user->ID); ?>" />
                <input type="hidden" id="after_success_redirect" name="after_success_redirect" value="<?php echo esc_url(site_url()."/update-subscription");?>">
                <button id="update_subscription_action" type="submit" class="btn btn-primary" ><?php _e('Submit', 'mm365') ?></button>
           </div>
        </div>
  </section>
</form>




  </div><!-- dash panel -->
</div><!--dash -->

<?php
get_footer();
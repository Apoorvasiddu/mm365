<?php
/**
 * Template Name: Close Match Request
 *
 */

$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole', ['business_user']);

$mr_id = $_REQUEST['mr_id'];
$nonce = $_REQUEST['_wpnonce'];
$act = $_REQUEST['act'];


if (!wp_verify_nonce($nonce, 'close_matchrequest')) {
  die(__('Unauthorised token', 'mm365'));
}


switch ($act) {
  case 'cancel':
    $closure_reasons = apply_filters('mm365_helper_get_themeoption', 'closure_reasons_cancelled');
    $display_mode = 'closure_ccancelled_display_mode';
    $reason_label = 'cancellation';
    break;

  case 'complete':
    $closure_reasons = apply_filters('mm365_helper_get_themeoption', 'closure_reasons');
    $display_mode = 'closure_completed_display_mode';
    $reason_label = 'completion';
    break;
}

get_header();
?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>
  <div class="dashboard-content-panel">

    <h1 class="heading-large pbo-10">
      <a href="#" onclick="history.back()"><img class="back-arrow"
          src="<?php echo get_template_directory_uri() ?>/assets/images/arrow-left.svg" height="36px" alt=""></a>
      <?php echo esc_html(ucfirst($act)); ?> match request
    </h1>
    <section class="company_preview"
      data-intro="Select the reason for <?php echo esc_html($reason_label); ?> of this match request along with a feedback message">
      <form method="post" id="mm365_matchrequest_close" action="#" data-parsley-validate enctype="multipart/form-data">
        <div class="form-row form-group">
          <div class="col-lg-6">
            <label for="">Reason for
              <?php echo esc_html($reason_label); ?><span>*</span>
            </label>
            <select required data-parsley-errors-container=".match_statusError" name="match_closure_filter"
              id="match_closure_filter" class="form-control mm365-single">
              <option value="">-Select-</option>
              <?php
              foreach ($closure_reasons as $key => $value) {
                if ($value[$display_mode] == 1) {
                  echo '<option>' . $value['reason_text'] . '</option>';
                }
              }
              ?>
            </select>
          </div>
        </div>

        <?php

        if ($act == 'complete'): ?>

          <!-- Contract details -->
          <div id="contract-details-block">
            <div class="form-row form-group">
              <div class="col-lg-3">
                <label for="">Approximate Contract Value<span>*</span></label><br />
                <input required type="text" name="contract_value" id="contract_value" class="form-control"
                  pattern="^\$\d{1,3}(,\d{3})*(\.\d+)?$" value="" data-type="currency" placeholder="$1,000,000.00">
              </div>
            </div>
            <div class="form-row form-group">
              <div class="col-lg-8">
                <label for="">Contract Terms & Conditions<span>*</span></label>
                <textarea name="contract_termsandconditions" id="contract_termsandconditions" class="form-control" id=""
                  required rows="3"></textarea>
              </div>
            </div>
          </div>

          <?php
          if (get_post_meta($mr_id, 'mm365_matchrequest_status', true) != 'auto-approved') {
            ?>
            <!-- Rating list here -->
            <div class="form-row form-group">
              <div class="col-lg-8">
                <label for="">Grade matched companies<span>*</span></label>
                <p>
                  Green: Perfect match / Established business <br />
                  Yellow: May consider this MBE in future requirements<br />
                  Red: We do not have business for this MBE <br />
                </p>
                <table class="matchrequests-list table table-striped" cellspacing="0" width="100%">
                  <thead class="thead-dark">
                    <tr>
                      <th>Company name</th>
                      <th>Your grade</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $matched_companies = maybe_unserialize(get_post_meta($mr_id, 'mm365_matched_companies', true));
                    $approved_companies = array();
                    foreach ($matched_companies as $key => $value) {
                      if ($value[1] == '1') {
                        array_push($approved_companies, $value[0]);
                      }
                    }
                    wp_reset_postdata();

                    $args = array(
                      'post_type' => 'mm365_companies',
                      'posts_per_page' => -1,
                      'orderby' => 'date',
                      'post__in' => $approved_companies
                    );
                    $get_approved_companies = new WP_Query($args);
                    while ($get_approved_companies->have_posts()):
                      $get_approved_companies->the_post();
                      ?>

                      <tr>
                        <td>
                          <?php echo get_the_title(); ?>
                        </td>
                        <td>
                          <!-- 1= Green, 2 = Yellow, 3= Red -->
                          <div>
                            <label class="radio-img" title="" data-toggle="tooltip" data-original-title="">
                              <input required data-parsley-errors-container=".gradeError<?php echo get_the_ID() ?>"
                                type="radio" name="<?php echo get_the_ID() ?>_grade" value="1">
                              <label></label>
                              <img src="<?php echo get_template_directory_uri() ?>/assets/images/green_grade.png">
                            </label>

                            <label class="radio-img" title="" data-toggle="tooltip" data-original-title="">
                              <input required data-parsley-errors-container=".gradeError<?php echo get_the_ID() ?>"
                                type="radio" name="<?php echo get_the_ID() ?>_grade" value="2">
                              <label></label>
                              <img src="<?php echo get_template_directory_uri() ?>/assets/images/yellow_grade.png">
                            </label>

                            <label class="radio-img" title="" data-toggle="tooltip" data-original-title="">
                              <input required data-parsley-errors-container=".gradeError<?php echo get_the_ID() ?>"
                                type="radio" name="<?php echo get_the_ID() ?>_grade" value="3">
                              <label></label>
                              <img src="<?php echo get_template_directory_uri() ?>/assets/images/red_grade.png">
                            </label>


                          </div>
                          <span class="gradeError<?php echo get_the_ID() ?>"></span>
                        </td>
                      </tr>
                      <?php
                    endwhile;

                    wp_reset_postdata();

                    ?>
                  <tbody>
                </table>
              </div>
            </div>
          <?php } ?>
          <!-- Rating list ends here -->
        <?php endif; ?>

        <div class="form-row form-group">
          <div class="col-lg-12">
            <label for="">Message<span>*</span>
              <?php if ($reason_label == 'completion'): ?>
                <br /><small>Please include the details such as Supplier name, Contract Awarded, Value, Term etc</small>
              <?php else: ?>
                <br /><small>Please include the details such as Supplier preffered, details of services products looking
                  for, contracts etc</small>
              <?php endif; ?>
            </label>

            <textarea name="reason_for_mrclosure" id="reason_for_mrclosure" class="form-control" id="" required
              rows="2"></textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="col-md-12 text-right">
            <input type="hidden" name="mr_id" id="mr_id" value="<?php echo esc_html($mr_id); ?>">
            <input type="hidden" name="act" id="act" value="<?php echo esc_html($act); ?>">
            <input type="hidden" name="redirect_to" id="redirect_url"
              value="<?php echo esc_url(site_url() . '/request-for-match'); ?>">
            <button type="submit" class="btn btn-primary">
              <?php _e('Submit', 'mm365') ?>
            </button>
          </div>
        </div>
      </form>
    </section>
  </div>
</div>
</div>

<?php
get_footer();
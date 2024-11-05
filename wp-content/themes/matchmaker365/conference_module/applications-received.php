
<?php
  //$conferenceApplicationsClass = new mm365_cm_councilmanager();
  $conf_id = $args['conf_id']; 
?>
<section id="applications-received-section" class="company_preview mto-30">
<div class="row clearfloats">
          <div class="col-md-5">
              <h3 id="mr" class="heading-medium text-center text-md-left ">Participation Applications</h3>
          </div>    
</div>

<!-- List Existing Match Requests class="matchrequests-list table table-striped " -->
<table id="certificates_list" class="matchrequests-list table table-striped " cellspacing="0" width="100%" data-intro="List of certificates submitted">
          <thead class="thead-dark">
            <tr>
              <th ><h6>Company</h6></th>
              <th class="no-sort"><h6>Deligates Registered</h6></th>
              <th class="no-sort"><h6>Deligates Count</h6></th>
              <th class="no-sort" width="40%"><h6>Covering Letter</h6></th>
              <th class="no-sort text-right"><h6></h6></th>
            </tr>
          </thead>
          <?php

        $applications = apply_filters('mm365_offline_conferences_applications_received', $conf_id);

        foreach ($applications as $applicant) {
            ?>
            <tr>
              <td><?php echo esc_html($applicant['name']); ?></td>
              <td class="no-sort"><?php echo wp_kses($applicant['deligates'], array( 'br' => array()) ); ?></td>
              <td class="no-sort"><?php echo esc_html($applicant['deligates_count']); ?></td>
              <td class="no-sort"><p><?php echo esc_html($applicant['covering_letter']); ?></p></td>
              <td class="no-sort text-right">
                <?php if($applicant['status'] == 'applied'): ?>
                <a data-application_id="<?php echo $applicant['application_id']; ?>"  href="#" class="councilAcceptParticipation btn btn-primary green text-light">Accept</a>
                <a data-application_id="<?php echo $applicant['application_id']; ?>" href="#" class="councilRejectParticipation btn btn-primary red text-light">Reject</a>
                <?php else:
                  $application_status = get_post_meta( $applicant['application_id'], 'status', true );
                  ?>
                  <span class="application-status <?php echo $application_status; ?>"><?php echo $application_status; ?></span>
                  
                <?php endif; ?>
              </td>
            </tr>
        <?php } ?>  
</table>
</section>



<!-- Popup form -->
<div id="rejectConfParticipationApplicationForm" class="conferences-apply_popup" style="display:none; min-width:400px">
    <form method="post" id="mm365_reject_offline_conf_particiaption" action="#"  data-parsley-validate enctype="multipart/form-data" >
        <h4>Reject Application</h4>
        <div class="form-row form-group   pto-10">
            <div class="col-12">
                        <label for="">Cause of rejection<span>*</span></label>
                        <textarea name="cause_of_rejection" class="form-control " required  ></textarea>
            </div>
        </div>

        <div class="form-row form-group">
            <div class="col-12 text-right">
                  <input type="hidden" id="pop_conf_id" name="conf_id" value="<?php echo $conf_id; ?>">
                  <input type="hidden" id="pop_application_id" name="application_id" value="">
                  <button id="applyConfParticiaption" type="submit" class="btn btn-primary" ><?php _e('Submit', 'mm365') ?></button>
            </div>
        </div>
    </form>
</div>
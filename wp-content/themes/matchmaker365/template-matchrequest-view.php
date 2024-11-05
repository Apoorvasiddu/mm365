<?php
/**
 * Template Name: Match Request View
 *
 */
$mr_id = $_REQUEST['mr_id']; 

$user = wp_get_current_user();
do_action('mm365_helper_check_loginandrole',['mmsdc_manager','business_user']);

//List of councils array
//$councils_list = $mm365_helper->councils_list();

  if(get_post_status($mr_id) == 'pending'){
    wp_redirect(site_url('request-for-match'));
  }

  get_header();
  
?>
<style>
  select.form-control,#councilFilter{
    display: inline;
    width: 200px;
    margin-left: 5px;
  }
  #matchresults-list_filter label:last-child{
    width:50%;
  }

  #matchresults-list_filter{
    display:flex;
    align-items:start;
  }
  #matchresults-list_filter label input{
    width:80%
  }
</style>
<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
</div>
<section class="dashboard-content-panel">
  <!-- Panel starts -->
 
<?php  
      //Check if post belongs to current user
      if((get_current_user_id() == get_post_field( 'post_author', $mr_id )))
            {
              $company_id     = get_post_meta($mr_id, 'mm365_requester_company_id', true );     
              $current_status = get_post_meta($mr_id, 'mm365_matchrequest_status', true); 
              $match_status = $current_status;

              switch ($match_status) {
                case 'nomatch':
                    $mr_stat_display = "<span class='".$match_status."'>No Match</span>";
                  break;
                case 'auto-approved':
                    $mr_stat_display = "<span class='".$match_status."'>Auto Approved</span>";
                break;
                default:
                    $mr_stat_display = "<span class='".$match_status."'>".ucfirst($match_status)."</span>";
                  break;
              }
?>

        <div class="row">
          <div class="col-md-8">
            <a href="<?php echo site_url(); ?>/request-for-match#mr" class=""><h3 class='heading-large'><img class="back-arrow" src="<?php echo get_template_directory_uri()?>/assets/images/arrow-left.svg" width="36px" height="36px" alt="">&nbsp;View Match</h3></a>
            <h4 class="heading-medium text-center text-md-left">Match Request Details</h4>
          </div>
          <div class="col-md-4 d-flex flex-column flex-md-row align-items-center justify-content-end">
          <?php
            $url         = site_url().'/matchresults-download?mr_id='.$mr_id;
            $closure_url = site_url().'/close-match?mr_id='.$mr_id;
            
            if(in_array($match_status,array('approved','auto-approved'))): ?>
                 <a data-intro="Cancel the match request, if you are not satisfied with the results" data-step="3" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'close_matchrequest' ), site_url().'/close-match?mr_id='.$mr_id.'&act=cancel' ); ?>" class="btn btn-primary red">Cancel Match Request</a>&nbsp;
                 <a data-intro="Complete the match request if you have successfully established a business with the supplier" data-step="4" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'close_matchrequest' ), site_url().'/close-match?mr_id='.$mr_id.'&act=complete' ); ?>" class="btn btn-primary green">Complete Match Request</a>&nbsp;
              <?php endif; ?>
            <a data-intro="Click to download all the details of the matched companies in Excel format" data-step="5" href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'download_results' ), $url ); ?>" class="btn btn-primary dash-report-btn">Download Results</a>
          </div>
        </div>

        <section class="matchrequest-short-details" data-intro="Quick details of the match request submitted" data-step="1" >
          <table class="table">
            <tbody>
            <tr>
                    <td width="18%">NAICS Codes</td>
                    <td>
                      <?php
                    foreach(get_post_meta($mr_id, 'mm365_naics_codes') as $naic){

                      echo '<a class="shorttag" target="_blank" href="https://www.naics.com/naics-code-description/?code='.$naic.'&v=2022" alt="Naics Code">'.$naic.'
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    

                      </a>';
                    }
                    ?></td>
                  </tr>
            <tr>
                    <td>Details of services or products you are looking for</td><td><?php echo get_post_meta($mr_id, 'mm365_services_details', true ); ?></td>
                  </tr>
                  <!-- <tr>
                    <td  width="18%">Company name</td><td><?php echo get_the_title($company_id); ?></td>
                  </tr> -->
                  <!-- <tr>
                    <td>Contact person</td><td><?php echo get_post_meta($company_id, 'mm365_contact_person', true ); ?></td>
                  </tr>
                  <tr>
                    <td>Phone number</td><td><?php echo get_post_meta($company_id, 'mm365_company_phone', true ); ?></td>
                  </tr>
                  <tr>
                    <td>Email ID</td><td><?php echo get_post_meta($company_id, 'mm365_company_email', true ); ?></td>
                  </tr> -->

                  <tr>
                    <td >Approved date & time</td><td>
                    <?php 
                    $approved_time = get_post_meta($mr_id, 'mm365_matched_companies_approved_time', true );
                    if($approved_time != '') echo date('m/d/Y g:i A', $approved_time );
                    
                     ?></td>
                 </tr>  
                 <tr class="matchrequests-list disable-shadow">
                    <td>Match request status</td><td><?php echo $mr_stat_display ?></td>
                  </tr> 
                 <?php if($match_status == 'closed'): ?>
                  <tr>
                    <td >Reason for closure</td><td><?php echo esc_html(get_post_meta($mr_id, 'mm365_reason_for_closure_filter', true )); ?></td>
                  </tr>
                  <tr>
                    <td >Message</td><td><?php echo esc_html(get_post_meta($mr_id, 'mm365_reason_for_closure', true )); ?></td>
                  </tr>    
                <?php endif; ?>
            </tbody>
          </table>
        </section>         
        
        <div class="row pto-30">
          <div class="col-md-6">
            <h4 class="heading-medium text-center text-md-left">Match Results</h4>
          </div>
          
        </div>  
       <?php

              $matched_companies =  maybe_unserialize(get_post_meta($mr_id, 'mm365_matched_companies', true ));
              $approved_companies = array();
              foreach ($matched_companies as $key => $value) {
                if($value[1]=='1'){
                  array_push($approved_companies,$value[0]);
                }
              }
        ?>
        <div class="table-responsive" data-intro="List of companies matched against your request. You can start communicating with the suppliers by clicking 'Schedule Meeting' link or click on the company name to see their full details " data-step="2">

          <!-- Council filter -->
          <div class="council-filter">
                <label id="councilFilter_label" for="councilFilter">Council:
                <select id="councilFilter" class="form-control">
                  <option value="">All Councils</option>
                  <?php
                      apply_filters('mm365_dropdown_councils', NULL,'shortname');
                  ?>
                </select>
                </label>
          </div>


          <table id="matchresults-list" class="matchrequests-list table table-striped">
                                <thead class="thead-dark">
                                  <tr>
                                    <th><h6>Company name</h6></th>
                                    <th><h6>Council</h6></th>
                                    <th width="10%"class="no-sort break-word"><h6>NAICS Codes</h6></th>
                                    <th width="25%"><h6>Company description</h6></th>
                                    <th class="no-sort" width="15%"><h6>Meeting status</h6></th>
                                    <th class="no-sort"><h6></h6></th>
                                  </tr>
            </thead>
            <tbody>
                    <?php  
                    apply_filters('mm365_get_approved_matches', $approved_companies,'user',$mr_id)
                    ?>
            </tbody>
        </table>
     </div>
  <?php  }  ?>
            </div>
  </div>
  <!-- Panel ends -->

</div>

<?php
get_footer();
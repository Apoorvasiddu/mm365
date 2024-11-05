<?php
/**
 * Template Name: Admin - Certificate Verification Listing
 *
 */

  $user = wp_get_current_user();

  $user_council_id = apply_filters('mm365_helper_get_usercouncil',$user->ID);
  
  do_action('mm365_helper_check_loginandrole',['business_user','council_manager','mmsdc_manager']);

  get_header();

  //$filter_status = $_REQUEST['stat'];
  (isset($_REQUEST['stat'])) ? $filter_status = $_REQUEST['stat'] : $filter_status = '';
  (isset($_REQUEST['period'])) ? $period = $_REQUEST['period'] : $period = '';

  if ( in_array( 'council_manager', (array) $user->roles ) ) {
    
    $council_id = apply_filters('mm365_helper_get_usercouncil',$user->ID);
    $heading_additional = esc_html(" - ".apply_filters('mm365_council_get_info',$council_id));
    
  } else $heading_additional = NULL;



  if(isset($_REQUEST['sacouncilfilter']) AND $_REQUEST['sacouncilfilter'] != '' AND $user_council_id ==''){
    //get id of council if
    $admin_council_filter   = $_REQUEST['sacouncilfilter'];
    //Add Council name to title
    $filtering_council = "".$mm365_helper->council_info($admin_council_filter)." ";

  }else{
    $admin_council_filter   = NULL;
    $filtering_council =  NULL;
  }
  
?>

<style>
  select.form-control,#councilFilter{
    display: inline;
    width: 200px;
    margin-left: 5px;
  }
  #admin_certificates_list_filter label:last-child{
    width:50%;
  }

  #admin_certificates_list_filter{
    display:flex;
    align-items:start;
    justify-content:flex-end;
  }
  #admin_certificates_list_filter label input{
    width:80%

  }
</style>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">


<section class="row admin-dash-filter">
    <div class="col-12"><h3 class="heading-large"><?php echo $filtering_council; ?>Certificate Verification <?php echo $heading_additional; ?> <?php if($filter_status != ''): echo "(".ucfirst($filter_status).")"; endif; ?></h3></div>
</section>
<?php  if($user_council_id == '' AND $admin_council_filter == ''): ?>
<!-- Council filter -->
<div class="council-filter">
      <label id="councilFilter_label" for="councilFilter">Council:
      <select id="councilFilter" class="form-control">
        <option value="">All Councils</option>
        <?php 
          apply_filters('mm365_dropdown_councils', NULL);
        ?>
      </select>
      </label>
</div>
<?php endif; ?>
<table data-intro="List of certificates submitted by the companies." id="admin_certificates_list" class="matchrequests-list table table-striped" data-period="<?php esc_html_e($period); ?>" data-statfilter="<?php esc_html_e($filter_status); ?>" data-sacouncilfilter="<?php echo $admin_council_filter; ?>" cellspacing="0" width="100%">
  <thead class="thead-dark">
    <tr>
      <th class="no-sort"><h6>Submitted by</h6></th>
      <?php  if($user_council_id == ''): ?><th class="no-sort"><h6>Council</h6></th><?php endif; ?>
      <th class="no-sort"><h6>Date uploaded</h6></th>
      <th class="no-sort"><h6>Expiration date</h6></th>
      <th  width="20%" class="no-sort"><h6>Notes</h6></th>
      <th><h6>Status</h6></th>
      <th class="no-sort"><h6></h6></th>
    </tr>
  </thead>
  <tbody></tbody>
</table>



  </div>
</div>

<?php
get_footer();
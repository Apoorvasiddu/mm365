<?php
/**
 * Template Name: View Reports - Meetings
 *
 */

  $user = wp_get_current_user();

  $council_id = apply_filters('mm365_helper_get_usercouncil', $user->ID);
  
  do_action('mm365_helper_check_loginandrole',['mmsdc_manager','council_manager','business_user']);

  get_header();
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">

  <?php
        $period = $_REQUEST['period'];

        if(isset($_REQUEST['sacouncilfilter']) AND $_REQUEST['sacouncilfilter'] != ''){
          //get id of council if
          $admin_council_filter   = $_REQUEST['sacouncilfilter'];
          //Add Council name to title
          $filtering_council = " (".apply_filters('mm365_council_get_info',$admin_council_filter).") ";

        }else{
          $admin_council_filter   = NULL;
          $filtering_council =  NULL;
        }
        

  ?>

<section class="row admin-dash-filter">
    <div class="col-12"><h3 class="page_main_heading">View - Meetings scheduled in the last one <?php echo $period.$filtering_council; ?></h3></div>
</section>
<table id="viewreports_meetings_admin" data-period="<?php echo $period; ?>" data-sacouncilfilter="<?php echo $admin_council_filter; ?>" class="matchrequests-list table table-striped" cellspacing="0" width="100%">
  <thead class="thead-dark">
    <tr>
      <th><h6>Title</h6></th>
      <th class="no-sort" class="no-sort"><h6>Buyer</h6></th>
      <th class="no-sort" class="no-sort"><h6>Supplier</h6></th>
      <th width="20%" class="no-sort"><h6>Meeting time</h6><small  class="show_user_tz"></small></th>
      <th class="no-sort"><h6>Meeting type</h6></th>
      <th class="no-sort"><h6>Status</h6></th>
    </tr>
  </thead>
  <tbody>

</tbody>
</table>



  </div>
</div>

<?php
get_footer();
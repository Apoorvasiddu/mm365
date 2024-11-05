<?php
/**
 * Template Name: Super Admin - List Councils
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
    <div class="col-12"><h3 class="heading-large">List Councils</h3></div>
</section>

<table id="superadmin_list_councils" class="mm365datatable-list table table-striped"  cellspacing="0" width="100%" data-intro="List of councils in the platform. You can edit the council details by clicking the 'Edit' link ">
  <thead class="thead-dark">
    <tr>
      <th><h6>Council Name</h6></th>
      <th class="no-sort"><h6>Location</h6></th>
      <th class="no-sort"><h6>Contact Person</h6></th>
      <th class="no-sort"><h6>Match Approval Privilege</h6></th>
      <th  width="20%" class="no-sort"><h6>Modified time</h6></th>
      <th class="no-sort"><h6></h6></th>
      <th class="no-sort"><h6></h6></th>
    </tr>
  </thead>
  <tbody>
  
  <?php
  $council_list = apply_filters('mm365_council_list',1);

  foreach ($council_list as $value) {
    ?>
    <tr>
      <td><?php echo esc_html($value['name']); ?></td>
      <td><?php echo esc_html($value['location']); ?></td>
      <td><?php echo esc_html($value['contact']); ?></td>
      <td><?php echo ($value['additional_permission'] == 1) ? "Yes": "No"; ?></td>
      <td><?php echo esc_html($value['modified']); ?></td>
      <td><a href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'sa_council_view' ), site_url().'/council-details?md=1&councilid='.esc_html($value['ID'])); ?>">VIEW</a>  </td>
      <td><a href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'sa_council_view' ), site_url().'/council-details?md=2&councilid='.esc_html($value['ID'])); ?>">EDIT</a></td>
    </tr>
<?php
  }
?>
  </tbody>
</table>


  </div>
</div>

<?php
get_footer();
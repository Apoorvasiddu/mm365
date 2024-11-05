<?php
/**
 * Template Name: Super Admin - List Council Managers
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
    <div class="col-12"><h3 class="heading-large">List Council Managers</h3></div>
</section>

<table id="superadmin_list_council_managers" class="mm365datatable-list table table-striped"  cellspacing="0" width="100%" data-intro="List of council managers">
  <thead class="thead-dark">
    <tr>
      <th data-intro="Council supervised by the Manager"><h6>Council</h6></th>
      <th class="no-sort" data-intro="Manager's name"><h6>Manager</h6></th>
      <th class="no-sort" data-intro="Manager's phone number"><h6>Phone</h6></th>
      <th  width="20%" class="no-sort"><h6>Email</h6></th>
      <th data-intro="User's login status. Only the ACTIVE users can login to the platform. INACTIVE users cannot login to the system. You can change the status from editing Council Manger page"><h6>Status</h6></th>
      <th class="no-sort" data-intro="Edit Council Manager"><h6></h6></th>
    </tr>
  </thead>
  <tbody>
  
  <?php
  $users  = get_users( array( 'role__in' => array( 'council_manager') ) );
  foreach ($users as $key => $value) {
    $users_council_id = apply_filters('mm365_helper_get_usercouncil',$value->ID);
    $council_name  = get_the_title($users_council_id);
    $user_lock_status = get_user_meta($value->ID, 'baba_user_locked', true );
    ?>
    <tr>
      <td><?php echo esc_html($council_name); ?></td>
      <td><?php echo esc_html($value->display_name); ?></td>
      <td><?php echo esc_html(get_user_meta($value->ID, '_mm365_dcm_phone', true )); ?></td>
      <td><?php echo esc_html($value->user_email); ?></td>
      <td>
         <span class="user_lock <?php echo esc_html($user_lock_status); ?>">
           <?php echo ($user_lock_status == '') ?  "ACTIVE" :  "INACTIVE"; ?>
         </span>
      </td>
      <td><a href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'sa_edit_council_manager' ), site_url().'/edit-council-manager?cmu='.esc_html($value->ID)); ?>">EDIT</a></td>
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
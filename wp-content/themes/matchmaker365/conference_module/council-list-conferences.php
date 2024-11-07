<?php
/**
 * Template Name: CM - Council List Conference
 *
 */

  $user = wp_get_current_user();

  do_action('mm365_helper_check_loginandrole',['council_manager']);

  get_header();


?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">


<section class="row admin-dash-filter">
    <div class="col-12"><h3 class="heading-large">Council Organized Conferences</h3></div>
</section>

<table id="cm_list_conferences" class="mm365datatable-list table table-striped"  cellspacing="0" width="100%" data-intro="Conferences created by council">
  <thead class="thead-dark">
    <tr>
      <th><h6>Title</h6></th>
      <!--<th><h6>Scope</h6></th>
      <th class="no-sort"><h6>Keywords</h6></th> -->
      <th class="no-sort"><h6>Date</h6></th>
      <!--<th class="no-sort"><h6>Approximate Business Value</h6></th> -->
      <th  width="20%" class="no-sort"><h6>Contact Person</h6></th>
      <th  width="20%" class="no-sort"><h6>Applicants Count</h6></th>
      <th class="no-sort"><h6></h6></th>
      <th class="no-sort"><h6></h6></th>
    </tr>
  </thead>
  <tbody>
  
  <?php
  $conferences = apply_filters('mm365_offline_conferences_list',TRUE,FALSE);
  $current_user = get_current_user_id();
  foreach ($conferences as $value) {
    ?>
    <tr>
      <td><?php echo esc_html($value['name']); ?></td>
      <!-- <td class="text-capitalize"><span class="badge <?php echo esc_html($value['scope']); ?>"><?php echo esc_html($value['scope']); ?></span></td>
      <td><?php echo esc_html($value['keywords']); ?></td> -->
      <td><?php echo esc_html($value['date']); ?></td>
      <!--<td><?php //echo esc_html($value['business_value']); ?></td> -->
      <td><?php echo wp_kses( $value['contact_person'], array('br' => array()) ); ?></td>
      <td><?php echo apply_filters('mm365_offline_conference_get_deligates_count',$value['ID']) ?></td>
      <td><a href="<?php echo add_query_arg( '_wpnonce', wp_create_nonce( 'view_offline_conf' ), site_url('view-offline-conference').'?conf_id='.$value['ID']); ?>">VIEW</a>  </td>
      <td><?php if($user->ID == $value['author_id']): ?><a href="<?php  echo add_query_arg( 'comoffconfnonce', wp_create_nonce( 'cm_edit_offline_conf_'.$value['ID'] ), site_url('cm-edit-offline-conference/?conf_id='.esc_html($value['ID']) ) ); ?>">EDIT</a><?php endif; ?></td>
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
<?php
/**
 * 
 * Template Name: Select Company
 *
 * List all the companies which user has registered
 */
$user = wp_get_current_user();

$councils_list = apply_filters('mm365_council_list',1);


get_header();
  
?>


<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">
     <!--  Table Goes here -->
     <?php 
       $companies_list = apply_filters('mm365_businessuser_companies_list',$user->ID);
     ?>

<section class="row admin-dash-filter">
    <div class="col-6"><h3 class="heading-large" data-hint="Please click select link to change to that company" data-hint-position="top-left">Your Companies</h3></div>
    <div class="col-6 d-flex align-items-center justify-content-end">
      <a href="<?php echo site_url('register-new-company') ?>" class="btn btn-primary" data-intro="Add a new company" data-step="2" data-position="left">Add Company</a>
    </div>
</section>

<table id="superadmin_list_council_managers" class="mm365datatable-list table table-striped"  cellspacing="0" width="100%" data-intro="List of your companies, click 'SELECT' to switch">
  <thead class="thead-dark">
    <tr>
      <th><h6>Company</h6></th>
      <th class="no-sort"><h6>Location</h6></th>
      <th class="no-sort"><h6>Council</h6></th>
      <th class="no-sort"><h6>Type</h6></th>
      <th class="no-sort"><h6></h6></th>
    </tr>
  </thead>
  <tbody>

   <?php 
   foreach ($companies_list as $key => $value) { 

    ?>
    <tr>
      <td>
         <span class="<?php echo (isset($_COOKIE['active_company_id']) AND ($value['id'] == $_COOKIE['active_company_id'])) ? 'selected':''; ?>">
         <?php echo esc_html(get_the_title($value['id'])); ?>
         </span>
      </td>
      <td>
      <?php
      echo apply_filters('mm365_helper_get_cityname', get_post_meta( $value['id'], 'mm365_company_city', true ),',')." ".
      apply_filters('mm365_helper_get_statename',get_post_meta( $value['id'], 'mm365_company_state', true ))." - ".
      apply_filters('mm365_helper_get_countryname', get_post_meta( $value['id'], 'mm365_company_country', true ));
      ?>
      </td>
      <td><?php echo apply_filters('mm365_council_get_info', get_post_meta( $value['id'], 'mm365_company_council', true)) ?></td>
      <td><?php esc_html_e(apply_filters('mm365_company_get_type',get_post_meta( $value['id'], 'mm365_service_type', true))); ?></td>
      <td><?php
              if(isset($_COOKIE['active_company_id']) != NULL){
                echo ($value['id'] == $_COOKIE['active_company_id']) ? '<span class="selected">SELECTED</span>': '<a class="switch_company" href="#" data-redirect="'.site_url('user-dashboard').'" data-companyid="'.$value['id'].'">SELECT</a>'; 
              }else{
                echo '<a class="switch_company" href="#" data-companyid="'.$value['id'].'" data-redirect="'.site_url('user-dashboard').'">SELECT</a>'; 
              }
      ?>
      </td>
    </tr>
     <?php } ?>

  </tbody>
</table>


  </div>
</div>

<div style="display: none;" id="companySelectionBox">
  <h4 class="text-center">Select a company to continue</h4>

  <table id="superadmin_list_council_managers" class="mm365datatable-list table table-striped"  cellspacing="0" width="100%">
    <thead class="thead-dark">
      <tr>
        <th><h6>Company</h6></th>
        <th class="no-sort"><h6>Council</h6></th>
        <th class="no-sort"><h6>Type</h6></th>
        <th class="no-sort"><h6></h6></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($companies_list as $key => $value) { 
        $council_id =  get_post_meta( $value['id'], 'mm365_company_council', true);
        $post_status = esc_html(get_post_status($value['id'])); 
      ?>
      <tr>
        <td><?php echo esc_html(get_the_title($value['id'])); ?><small class="text-danger"><?php echo ($post_status == 'draft') ? ' - Unlisted':''; ?></small></td>
        <td><?php echo apply_filters('mm365_council_get_info', $council_id) ?></td>
        <td><?php echo apply_filters('mm365_company_get_type', get_post_meta( $value['id'], 'mm365_service_type', true)) ; ?></td>
        <td><?php 
        if(isset($_COOKIE['active_company_id']) != NULL){
          echo ($value['id'] == $_COOKIE['active_company_id']) ? 'SELECTED': '<a class="switch_company" href="#" data-companyid="'.$value['id'].'" data-redirect="'.site_url('user-dashboard').'">SELECT</a>'; 
        }else{
          echo '<a class="switch_company" href="#" data-companyid="'.$value['id'].'" data-redirect="'.site_url('user-dashboard').'">SELECT</a>'; 
        }
        ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <p>You can switch between companies once selected.</p>

</div>

<?php
get_footer();
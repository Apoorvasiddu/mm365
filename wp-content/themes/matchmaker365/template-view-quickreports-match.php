<?php
/**
 * Template Name: View Reports - Match Requests
 *
 */
$user = wp_get_current_user();

$council_id = apply_filters('mm365_helper_get_usercouncil', $user->ID);

do_action('mm365_helper_check_loginandrole',['mmsdc_manager','council_manager']);

get_header();
?>

<style>
  select.form-control,#councilFilter{
    display: inline;
    width: 200px;
    margin-left: 5px;
  }
  #viewreports_matchlist_admin_filter label:last-child{
    width:50%;
  }

  #viewreports_matchlist_admin_filter{
    display:flex;
    align-items:start;
    justify-content:end;
  }
  #viewreports_matchlist_admin_filter label input{
    width:80%

  }
</style>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part( 'template-parts/dashboard','navigation' ); ?>
    
  </div>
  <div class="dashboard-content-panel">

    <?php
        $period = $_REQUEST['period'];
        $meta   = $_REQUEST['meta'];

        if(isset($_REQUEST['sacouncilfilter']) AND $_REQUEST['sacouncilfilter'] != ''){
          //get id of council if
          $admin_council_filter   = $_REQUEST['sacouncilfilter'];
          //Add Council name to title
          $filtering_council = " (".apply_filters('mm365_council_get_info',$admin_council_filter).") ";

        }else{
          $admin_council_filter   = NULL;
          $filtering_council =  NULL;
        }
        

        if($meta != 'x'){
          switch ($meta) {
            case 'auto-approved':
              $page_title      = 'auto approved';
              $approver_column = '<th class="no-sort"><h6>Approved by</h6></th>';
            break;
            case 'nomatch':
              $page_title      = 'without any matches';
              $approver_column = '<th class="no-sort"><h6>Approved by</h6></th>';
            break;            
            default:
               $page_title      = $meta;
               $approver_column = '<th class="no-sort"><h6>Approved by</h6></th>';
              break;
          }
          
        }else{ $page_title = ''; $approver_column = '<th class="no-sort"><h6>Approved by</h6></th>'; }
    ?>

<section class="row admin-dash-filter">
    <div class="col-12"><h3 class="page_main_heading">View - Match requests <?php echo $page_title; ?> in last one <?php echo $period.$filtering_council ; ?></h3></div>
</section>

<!-- Council filter -->
<?php if($council_id == ''  AND $admin_council_filter == ''): ?>
<div class="council-filter">
      <label id="councilFilter_label" for="councilFilter">Council:
      <select id="councilFilter" class="form-control">
        <option value="">All Councils</option>
        <?php 
          apply_filters('mm365_dropdown_councils', NULL)
        ?>
      </select>
      </label>
</div>
<?php endif; ?>
<table id="viewreports_matchlist_admin" data-period="<?php echo $period; ?>" data-matchmeta="<?php echo $meta; ?>" data-sacouncilfilter="<?php echo $admin_council_filter; ?>" class="matchrequests-list table table-striped" cellspacing="0" width="100%">
  <thead class="thead-dark">
    <tr>
      <th><h6>Requester Company</h6></th>
      <?php if($council_id == ''): ?><th class="no-sort"><h6>Council</h6></th><?php endif; ?>
      <th class="no-sort"><h6>Services or Products</h6></th>
      <th class="no-sort"><h6>Industry</h6></th>
      <th  width="20%" class="no-sort"><h6>Request details</h6></th>
      <th ><h6>Match status</h6></th>
      <?php if($meta == 'completed' OR $meta == 'cancelled'): ?>
      <th ><h6>Reason for closure</h6></th>
      <th class="no-sort"><h6>Message</h6></th>
      <?php endif;?>
      <th width="30%" class="no-sort"><h6>Matched companies</h6></th>
      <?php echo $approver_column; ?>
      <th><h6>Date</h6></th>
    </tr>
  </thead>
  <tbody>

</tbody>
</table>



  </div>
</div>

<?php
get_footer();
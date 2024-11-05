<?php
$user = wp_get_current_user();

$council_id = apply_filters('mm365_helper_get_usercouncil',$user->ID);
?>

<style>
  select.form-control,#councilFilter{
    display: inline;
    width: 200px;
    margin-left: 5px;
  }
  #matchpreference_admin_filter label:last-child{
    width:50%;
  }

  #matchpreference_admin_filter{
    display:flex;
    align-items:start;
    justify-content:flex-end;
  }
  #matchpreference_admin_filter label input{
    width:80%

  }
</style>
<h3 class='heading-large'>Set Buyer Match Preference</h3>
<!-- List Existing Match Requests -->
<div class="matchrequests-admin-view">
<?php if($council_id == ''): ?>
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
<table id="matchpreference_admin" class="matchrequests-list table table-striped" cellspacing="0" width="100%" data-intro="List of all the companies. If you want enable auto approval for match request for any of the listed companies, click on the check box next to 'Enabled' in approval required column. Once clicked the staus will be changed. This action can be toggled at manager’s discretion">

  <thead class="thead-dark">
    <tr>
      <!-- <th>#</th> -->
      <th width="40%"><h6>Company name</h6></th>
      <th class="no-sort"><h6>Contact info</h6></th>
      <?php if($council_id == ''): ?><th class="no-sort" width="40%"><h6>Council</h6></th><?php endif; ?>
      <th width="30%" ><h6>Type</h6></th>
      <th width="20%" ><h6>Auto Approval</h6></th>
      <th class="no-sort"><h6>Changelog</h6></th>
      
    </tr>
  </thead>
  <tbody>

</tbody>
</table>
</div>
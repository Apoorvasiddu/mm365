<?php
$user = wp_get_current_user();

$council_id = apply_filters('mm365_helper_get_usercouncil',$user->ID);

?>

<h3 class='heading-large'><?php echo (current_user_can('council_manager_approvers')) ? "Approve ":"" ?>Match Requests <?php echo esc_html(" - ".apply_filters('mm365_council_get_info',$council_id)); ?></h3>
<!-- List Existing Match Requests -->
<div class="matchrequests-admin-view">


<table id="matchlist_council_manager" class="matchrequests-list table table-striped" cellspacing="0" width="100%" data-intro="List of match requests submitted by the users">
  <thead class="thead-dark">
    <tr>
      <!-- <th>#</th> -->
      <th width="12%" data-intro="Submitted company"><h6>Company name</h6></th>
      <th data-intro="Company type"><h6>Type</h6></th>
      <th data-intro="Date and time of submission"><h6>Requested date & time</h6></th>
      <th data-intro="Location where the services or products are needed"><h6>Location</h6></th>
      <th data-intro="Keywords which the user has searched" width="30%"><h6>Details of services or products looking for	</h6></th>
      <th data-intro="Current status of match request. Please do not ignore the approved match requests as they might have few companies approved by super admin. You can approve more companies or shall add more companies to result set if required"><h6>Status</h6></th>
      <th class="no-sort"></th>
      <th data-intro="Click 'View Match' to see all the companies matched against the request." class="no-sort"></th>
      
    </tr>
  </thead>
  <tbody>

</tbody>
</table>
</div>
<?php
/**
 * Template Name: SB - Meetings
 *
 */
$user = wp_get_current_user();

do_action('mm365_helper_check_loginandrole',['super_buyer']);

//$sbpublicClass = new mm365_SuperBuyerPublic();


get_header();

?>

<div class="dashboard">
  <div class="dashboard-navigation-panel">
    <!-- Users Menu -->
    <?php get_template_part('template-parts/dashboard', 'navigation'); ?>
  </div>


  <div class="dashboard-content-panel">
    <h2 class="heading-large">Meetings By Team Members</h2>

    <!-- List of meeting s proposed by buyer team member goes here -->
    <section class="company_preview">


      <table id="buyer_teams_meetinglist" data-timezone="0" data-offset="0" data-dst="0"
        class="matchrequests-list table table-striped" cellspacing="0" width="100%">
        <thead class="thead-dark">
          <tr>
            <!-- <th>#</th> -->
            <th>
              <h6>Buyer</h6>
            </th>
            <th>
              <h6>Supplier</h6>
            </th>
            <th class="no-sort">
              <h6>Supplier' Council</h6>
            </th>
            <th class="no-sort">
              <h6>Contact person</h6>
            </th>
            <th class="no-sort" width="20%">
              <h6>Meeting title</h6>
            </th>
            <th class="no-sort">
              <h6>Date & Time</h6>
            </th>
            <th>
              <h6>Current status</h6>
            </th>
            <th class="no-sort"></th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>

    </section>


  </div>
</div>

<?php
get_footer();
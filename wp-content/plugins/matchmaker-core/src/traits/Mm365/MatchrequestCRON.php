<?php
namespace Mm365;

/**
 * CRON Jobs related to match requests
 * 
 */
trait MatchrequestCRON
{
    use NotificationAddon;

    /**
     * Notify super admin about the number of match request 
     * pending to be approved/processed
     * 
     * 
     */

    function mm365_notfication_pendingmatchrequests($args)
    {

        //Count of pending match requets for last 24 hours
        $week_query_args = array(
            'posts_per_page' => -1,
            // No limit
            'fields' => 'ids',
            // Reduce memory footprint
            'post_type' => 'mm365_matchrequests',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'mm365_matchrequest_status',
                    'value' => 'pending',
                    'compare' => '=',
                ),
            )

        );

        $week_query = new \WP_Query($week_query_args);
        $count_pending_approvals = $week_query->found_posts;
        //Send to all mmsdc_magaer user roles
        $to = '';
        $users = get_users(array('role__in' => array('mmsdc_manager')));
        foreach ($users as $user) {
            $email = $user->user_email;
            $to .= "," . $email;
        }

        $link = site_url() . '/admin-matchrequests-listing/';

        if ($count_pending_approvals > 1):
            $subject = 'You have ' . $count_pending_approvals . ' new Match Requests to approve';
            $title = $subject;
            $content = '
                    <p>Hi Super Administrator,</p>
                    <p>There are ' . $count_pending_approvals . ' new matches in Matchmaker365, which are pending for approval.  Please click on the below button to view the details and approve the request.</p>';
        else:
            $subject = $count_pending_approvals . ' Pending Match Approval';
            $title = 'You have a new Match Request to approve';
            $content = '<p>Hi Super Administrator,</p>
                    <p>There is ' . $count_pending_approvals . ' new match in Matchmaker365, which is pending for approval.  Please click on the below button to view the details and approve the request.</p>';
        endif;

        //$body = $this->mm365_email_body($title, $content, $link, 'View Pending Matches');
         $body = $this->mm365_email_body_template($title, $content, $link, 'View Pending Matches');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if ($count_pending_approvals > 0) {
            wp_mail($to, $subject, $body, $headers);
        }



    }

    /**
     * Notify requesters to close match request
     * 
     */

    function mm365_notfication_matchrequestclosure($args_8)
    {


        //Get list of 'approved' match requests which crossed more than 7 days from last update
        $args = array(
            'post_type' => 'mm365_matchrequests',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'order' => 'DESC',
        );

        $args['meta_query'] = array(
            array(
                array(
                    'key' => 'mm365_matchrequest_status',
                    'value' => array('approved', 'auto-approved'),
                    'compare' => 'IN'
                ),
            )
        );

        $args['date_query'] = array(
            array(
                'before' => '1 month ago',
                'column' => 'post_modified_gmt',
                'inclusive' => true,
            ),
        );

        $match_query = new \WP_Query($args);

        $totalData = $match_query->found_posts;

        //MRs array

        $open_matchrequests = array();

        if ($match_query->have_posts()) {
            while ($match_query->have_posts()) {

                $match_query->the_post();

                $keywords_searched = get_post_meta(get_the_ID(), 'mm365_services_details', true);
                $requester_company_id = get_post_meta(get_the_ID(), 'mm365_requester_company_id', true);

                $submitted_date = get_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated', true);
                $status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);

                //Email to send
                $requester_cmp_email = get_post_meta($requester_company_id, 'mm365_company_email', true);

                //Group by ID
                $open_matchrequests[] = array("id" => $requester_company_id, "title" => $keywords_searched, "date" => $submitted_date, 'status' => $status);

            }

            $grouped_arr = array();
            foreach ($open_matchrequests as $key => $item) {
                $grouped_arr[$item['id']][$key] = $item;
            }
            ksort($grouped_arr, SORT_NUMERIC);

            //Count
            foreach ($grouped_arr as $key => $value) {
                //echo $key.'='.count($value).'<br/>';

                $subject = 'Your match request is still open!!';

                $content = '<p>Hi ' . get_post_meta($key, 'mm365_company_name', true) . ',<br/> Thank you for your recent 
            Match Request submissions in Matchmaker365 (MM365).  
            To help the MMSDC with capturing key performance indicators for MM365, we’d like to remind you to please "Complete"
            your Match Request once your sourcing activity is finished.</p> 
            <table class="table" style="border-collapse: collapse;">
            <thead class="thead-dark">
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Details of services or products you are looking for
                </th>                    
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Requested On</th>
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Status</th>
            </thead>';

                foreach (array_slice($value, 0, 3) as $mr_data) {
                    $content .= '<tr>
                  <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px;">' . $mr_data['title'] . '</td>
                  <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px;">' . $mr_data['date'] . '</td>
                  <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px; text-transform:capitalize">' . $mr_data['status'] . '</td>
                </tr>';
                }

                $content .= '</table>';

                if (count($value) > 3) {
                    $content .= '<p> and <strong>' . (count($value) - 3) . '</strong> other match requests are waiting to be completed.</p>';
                }

                $content .= '<p>To do this, simply access your Match Request in MM365 (link), and at the top right of the screen are two buttons:
              "Cancel Match Request" and "Complete Match Request",
            Click on either option and enter in details regarding the resolution of your request.  
            As a reminder, this information is meant to assist us in our matchmaking support, and 
            allow us to capture successes of the platform in aggregate, as well as provide us opportunities 
            to direct any resources where they may be of assistance to our users.</p>
            <p>Please click on the below button to login and complete your match request.</p>';

                $requester_cmp_email = get_post_meta($key, 'mm365_company_email', true);
                
                //$body = $this->mm365_email_body($subject, $content, site_url('login'), 'Login');
                $body = $this->mm365_email_body_template($subject, $content, site_url('login'), 'Login');
                $headers = array('Content-Type: text/html; charset=UTF-8');

                //Trigger email to all requesters
                if (wp_mail($requester_cmp_email, $subject, $body, $headers)) {
                    error_log("Close match request email to " . $requester_cmp_email . " sent!");
                } else {
                    error_log("Close match request email to " . $requester_cmp_email . " to send!");
                }

            }

        }


    }


}

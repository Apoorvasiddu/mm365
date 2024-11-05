<?php
namespace Mm365;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class ConsolidatedReport
{
    use CouncilAddons;
    use NotificationAddon;

    function __construct()
    {

        //Load assets
        add_action('wp_enqueue_scripts', array($this, 'assets'), 11);

         //Listing 
         add_action( 'wp_ajax_view_consolidated_report', array( $this, 'view_consolidated_report' ) );


         add_filter('mm365_download_consolidated_report',array($this, 'download_consolidated_report' ), 10, 0);

    }

    /**
     * 
     * 
     */
    function assets()
    {

        wp_register_script('mm365_consolidated_report', plugins_url('matchmaker-core/assets/mm365_consolidated_report.js'), array('jquery'), false, TRUE);
        wp_enqueue_script('mm365_consolidated_report');
        wp_localize_script('mm365_consolidated_report', 
        'consolidatedrepAjax', 
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce("consolidatedrep_ajax_nonce")
        )
        );

    }



    /**
     * 
     * View consolidated report
     * 
     */

    public function view_consolidated_report()
    {


        $nonce = $_POST['nonce'];
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];

        if (!wp_verify_nonce($nonce, 'consolidatedrep_ajax_nonce')) {
            die();
        }


        $consolidated_report = $this->report(date('Y-m-d', strtotime($from_date)), date('Y-m-d', strtotime($to_date)));
        //List of councils - returns NAME and SHORTNAME
        $councils = $this->get_councils_list();

        $report = '';
        foreach ($consolidated_report as $key => $value) {
            $report .= '
        <tr>
            <td><h6>' . $councils[$key][0] . '</h6></td>
            <td>' . $value[0] . '</td>
            <td>' . $value[1] . '</td>
            <td>' . $value[2] . '</td>
            <td>' . $value[6] . '</td>
            <td>' . $value[7] . '</td>
            <td>' . $value[3] . '</td>
            <td>' . $value[4] . '</td>
            <td>' . $value[5] . '</td>
        </tr>';
        }
        echo $report;
        wp_die();

    }



    /**
     * 
     * Download Consolidated Report
     * 
     */
    public function download_consolidated_report()
    {

        //$nonce     = $_POST['nonce'];
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];

        $consolidated_report = $this->report(date('Y-m-d', strtotime($from_date)), date('Y-m-d', strtotime($to_date)));
        //List of councils - returns NAME and SHORTNAME
        $councils = $this->get_councils_list();

        $writer = new XLSXWriter();

        $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 50, 'wrap_text' => true);
        $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 20);
        $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');

        $writer->writeSheetHeader(
            'Sheet1',
            array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string', '9' => 'string'),
            $col_options = ['widths' => [30, 30, 30, 30, 30, 30, 30, 30, 30], 'suppress_row' => true]
        );

        $writer->writeSheetRow(
            'Sheet1',
            array(
                "Consolidated Report - \n" .
                'From ' . date_format(date_create($from_date), 'm/d/Y') . " " .
                'To ' . date_format(date_create($to_date), 'm/d/Y')
            ),
            $styles1
        );

        $writer->writeSheetRow(
            'Sheet1',
            $rowdata = array(
                'Council',
                'Suppliers registered',
                'Buyers Registered',
                'Pending Match requests',
                'Approved Match requests',
                'Auto Approved Match requests',
                'Completed Match requests',
                'Cancelled Match requests',
                'Meetings Scheduled',
            ),
            $styles2
        );


        //Prepare data array
        foreach ($consolidated_report as $key => $value) {
            $data[] = array($councils[$key][0], $value[0], $value[1], $value[2], $value[6], $value[7], $value[3], $value[4], $value[5]);
        }

        //Write XLS
        foreach ($data as $dat) {
            $writer->writeSheetRow('Sheet1', $dat, $styles3);
        }


        $file = 'Consolidated Report - ' . time() . '.xlsx';
        $writer->writeToFile($file);

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);
            exit;
        }


        wp_reset_postdata();
        die();


    }


    /**
     * 
     * Email consolidated report weekly through CRON job
     * 
     */


    public function email_report()
    {

        $consolidated_report = $this->report(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
        //List of councils - returns NAME and SHORTNAME
        $councils = $this->get_councils_list();


        $report = '
           <table class="table" style="border-collapse: collapse;">
            <thead class="thead-dark">
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Council</th>                    
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Suppliers registered</th>
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Buyers Registered</th>
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Pending Match requests</th>
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Completed Match requests</th>
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Cancelled Match requests</th>
                <th style="border: 1px solid #dddddd;text-align: center; font-size:12px; background:#356ab3; color:#fff;">Meetings Scheduled</th>
            </thead>';
        foreach ($consolidated_report as $key => $value) {
            $report .= '
            <tr>
                <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px;">' . $councils[$key][0] . '</td>
                <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px;">' . $value[0] . '</td>
                <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px;">' . $value[1] . '</td>
                <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px;">' . $value[2] . '</td>
                <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px;">' . $value[3] . '</td>
                <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px;">' . $value[4] . '</td>
                <td style="border: 1px solid #dddddd;text-align: center;padding:5px 2px;">' . $value[5] . '</td>
            </tr>';
        }
        $report .= '</table>';

        $subject = 'MatchMaker365 Consolidated Weekly Report From  ' . date('m/d/Y', strtotime('-7 days')) . " To " . date('m/d/Y');
        $content = '<p>Hi Super Administrator,</p>
              <p>Please find below Matchmaker365 Consolidated Weekly Report from ' . date('m/d/Y', strtotime('-7 days')) . " to " . date('m/d/Y') . '</p><br/>' . $report .
            '<p>Login to view the details</p>';

        //$body = $this->mm365_email_body($subject, $content, site_url('login'), 'Login');
         $body = $this->mm365_email_body_template($subject, $content, site_url('login'), 'Login');
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $users = get_users(array('role__in' => array('mmsdc_manager')));
        foreach ($users as $user) {
            $email = $user->user_email;
            $to .= "," . $email;
        }

        //Trigger email to all super admins
        if (wp_mail($to, $subject, $body, $headers)) {

            error_log("Consolidated report email sent!");

        } else {
            error_log("Consolidated report email failed to send!");
        }
    }


    /**
     * 
     * Report
     * 
     */
    public function report($from, $to)
    {

        //Get Council IDS
        $args = array(
            'post_type' => 'mm365_msdc',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'fields' => 'ids'
        );
        $loop = new \WP_Query($args);
        $councils_list = array();
        while ($loop->have_posts()):
            $loop->the_post();
            $councils_list[] = get_the_ID();
        endwhile;
        wp_reset_postdata();

        //Loop through council

        foreach ($councils_list as $council_id) {

            //Sellers total count
            $sellers_count = $this->companies_count($from, $to, $council_id, 'seller');

            //Buyers total count
            $buyers_count = $this->companies_count($from, $to, $council_id, 'buyer');

            //Match requests pending
            $pending_mr_count = $this->matchrequests_count($from, $to, $council_id, 'pending');

            //Completed Match requests 
            $completed_mr_count = $this->matchrequests_count($from, $to, $council_id, 'completed');

            //Cancelled Match requests 
            $cancelled_mr_count = $this->matchrequests_count($from, $to, $council_id, 'cancelled');

            //Meetings count
            $scheduled_count = $this->meetings_count($from, $to, $council_id, 'scheduled');
            $rescheduled_count = $this->meetings_count($from, $to, $council_id, 'rescheduled');


            $meetings_total_count = $scheduled_count + $rescheduled_count;

            //items thats are Not send in email
            $approved_matchrequests = $this->matchrequests_count($from, $to, $council_id, 'approved');

            $auto_approved_matchrequests = $this->matchrequests_count($from, $to, $council_id, 'auto-approved');

            //Do not change the sequence
            $data[$council_id] = array(
                $sellers_count,
                $buyers_count,
                $pending_mr_count,
                $completed_mr_count,
                $cancelled_mr_count,
                $meetings_total_count,
                $approved_matchrequests,
                $auto_approved_matchrequests
            );
        }

        return $data;
    }


    /**
     * 
     * Companies Count
     * 
     */
    public function companies_count($from, $to, $council_id, $type)
    {

        $from_date = date("Y-m-d", strtotime($from));
        $to_date = date("Y-m-d", strtotime($to));
        $toDate = date_parse_from_format("Y-m-d", $to_date);

        $count_args = array(
            'posts_per_page' => -1,
            // No limit
            'fields' => 'ids',
            // Reduce memory footprint
            'post_type' => 'mm365_companies',
            'post_status' => array('publish'),
            'date_query' => array(
                array(
                    'column' => 'post_date',
                    'after' => $from_date,
                    'before' => array(
                        'year' => $toDate['year'],
                        'month' => $toDate['month'],
                        'day' => $toDate['day'],
                    ),
                    'inclusive' => true,
                )
            ),
            'meta_query' => array(
                array(
                    'key' => 'mm365_service_type',
                    'value' => $type,
                    'compare' => '='
                ),
                array(
                    'key' => 'mm365_company_council',
                    'value' => $council_id,
                    'compare' => '='
                )
            )

        );
        $count_query = new \WP_Query($count_args);
        return $count_query->found_posts;

    }

    /**
     * 
     * Match Requests Count
     * 
     */
    public function matchrequests_count($from, $to, $council_id, $status = NULL)
    {

        $from_date = date("Y-m-d", strtotime($from));
        $to_date = date("Y-m-d", strtotime($to));
        $toDate = date_parse_from_format("Y-m-d", $to_date);

        if ($status == NULL) {
            $stat_param = array();
        } else {
            $stat_param = array(
                'key' => 'mm365_matchrequest_status',
                'value' => $status,
                'compare' => '='
            );
        }

        $count_args = array(
            'posts_per_page' => -1,
            // No limit
            'fields' => 'ids',
            // Reduce memory footprint
            'post_type' => 'mm365_matchrequests',
            'post_status' => array('publish'),
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => $from_date,
                    'before' => array(
                        'year' => $toDate['year'],
                        'month' => $toDate['month'],
                        'day' => $toDate['day'],
                    ),
                    'inclusive' => true,
                )
            ),
            'meta_query' => array(
                array(
                    'key' => 'mm365_requester_company_council',
                    'value' => $council_id,
                    'compare' => '='
                ),
                $stat_param
            )

        );
        $count_query = new \WP_Query($count_args);
        return $count_query->found_posts;

    }

    /**
     * 
     * Meetings count
     * 
     */

    public function meetings_count($from, $to, $council_id, $status)
    {

        $from_date = date("Y-m-d", strtotime($from));
        $to_date = date("Y-m-d", strtotime($to));
        $toDate = date_parse_from_format("Y-m-d", $to_date);

        $count_parameters = array(
            'posts_per_page' => -1,
            // No limit
            'fields' => 'ids',
            // Reduce memory footprint
            'post_type' => 'mm365_meetings',
            'post_status' => array('publish'),
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => $from_date,
                    'before' => array(
                        'year' => $toDate['year'],
                        'month' => $toDate['month'],
                        'day' => $toDate['day'],
                    ),
                    'inclusive' => true,
                )
            ),
            'meta_query' => array(
                array(
                    'key' => 'mm365_meeting_status',
                    'value' => $status,
                    'compare' => '='
                ),
                array(
                    'key' => 'mm365_attendees_council_id',
                    'value' => $council_id,
                    'compare' => '='
                )
            )

        );
        $count_query = new \WP_Query($count_parameters);
        return $count_query->found_posts;

    }



}
<?php
namespace Mm365;

trait ReportsCRON
{

    use CouncilAddons;
    use NotificationAddon;
    
    /**
     * Consolidated report (Super Admin)
     * 
     */
    function mm365_sa_consolidated_report_mail()
    {

        $consolidated_report = new ConsolidatedReport();
        $consolidated_report->email_report();

    }

    /**
     * Number of companies registred
     * 
     * 
     */
    function mm365_notfication_companycount_cm($args_7)
    {

        //Get consolidated report
        $consolidated_report_class = new ConsolidatedReport();
        $consolidated_report = $consolidated_report_class->report(date('Y-m-d', strtotime('-1 day')), date('Y-m-d'));

        $counts_and_emails = array();
        //get council managers list
        $get_council_managers = get_users(array('role__in' => array('council_manager')));
        foreach ($get_council_managers as $user) {
            $cm[$user->user_email] = $this->get_userDC($user->ID);
        }
        $emails_tosend = '';

        foreach ($consolidated_report as $key => $value) {
            // Key is council id - value is array with counts
            //Email ids to send per council
            $emails_tosend = implode(",", array_keys(array_intersect($cm, [$key])));
            $total_companies = ($value[0] + $value[1]);
            //Prepare the array  
            if ($total_companies > 0 and $emails_tosend != '') {
                //For each council count of suppliers and buyers
                $counts_and_emails[$key] = array(
                    'emails' => $emails_tosend,
                    'suppliers' => $value[0],
                    'buyers' => $value[1],
                );
            }

        }
        //Emailing the details
        $link = site_url() . '/council-dashboard/';
        foreach ($counts_and_emails as $key => $value) {

            $council_shortname = get_post_meta($key, 'mm365_council_shortname', true);

            $subject = 'You have ' . ($value['suppliers'] + $value['buyers']) . ' new user registrations today in Matchmaker365 for ' . $council_shortname;
            $title = $subject;
            $content = '<p>Hi ' . $council_shortname . ' Council Manager,</p>
                            <p>You have ' . ($value['suppliers'] + $value['buyers']) . ' new user registrations today in Matchmaker365 for ' . $council_shortname . '.</p>
                            <p>Please click on the below button to login and view the details.</p>';

           // $body = $this->mm365_email_body($title, $content, $link, 'Dashboard');
             $body = $this->mm365_email_body_template($title, $content, $link, 'Dashboard');
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($value['emails'], $subject, $body, $headers);

        }


    }


}
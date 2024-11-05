<?php
namespace Mm365;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Various CRON jobs
 * 
 */

class ScheduledTasks
{
    use MatchrequestCRON;
    use CertificationCRON;
    use ReportsCRON;
    use SubscriptionCRON;

    public function __construct()
    {
        add_filter('cron_schedules', array($this, 'mm365_cron_add_tenminutes'));

        //match requests
        add_action('mm365_event_pendingmatch_notification', [$this, 'mm365_notfication_pendingmatchrequests'], 10, 1);
        add_action('mm365_event_matchrequestclosure_mail', [$this, 'mm365_notfication_matchrequestclosure'], 10, 1);

        //Certificate
        add_action('mm365_event_certificate_expiry_notification', [$this, 'mm365_certificate_expiry_notification'], 10, 1);
        add_action('mm365_event_certificate_put_to_expiry', [$this, 'mm365_certificate_put_to_expiry'], 10, 1);
        add_action('mm365_event_certificate_notify_expiry', [$this, 'mm365_certificate_notify_expiry'], 10, 1);
        add_action('mm365_event_admin_notify_pending_ceritifcates', [$this, 'mm365_admin_notify_pending_ceritifcates'], 10, 1);

        //Reports
        add_action('mm365_event_sa_consolidated_report_mail', [$this,'mm365_sa_consolidated_report_mail'], 10, 1);
        add_action('mm365_event_cm_companiescount_mail', [$this,'mm365_notfication_companycount_cm'], 10, 1);

        //Subscription
        add_action('mm365_event_notify_subscription_expiry_mail', [$this,'notify_subscription_expiry'], 10, 1);
        add_action('mm365_event_put_subscription_to_expiry', [$this,'put_subscription_to_expiry'], 10, 1);

        $this->mm365_schedule_activator();

    }


    /**
     * custom intervals
     * 
     * 
     */
    function mm365_cron_add_tenminutes()
    {

        // Once a day
        $schedules['1440min'] = array(
            'interval' => 1440 * MINUTE_IN_SECONDS,
            'display' => __('Once a day')
        );

        // 4 times a day
        $schedules['four_times_daily'] = array(
            'interval' => 360 * MINUTE_IN_SECONDS,
            'display' => __('4 times a day'),
        );

        // 4 times a day
        $schedules['once_a_month'] = array(
            'interval' => 43200 * MINUTE_IN_SECONDS,
            'display' => __('Once A Month'),
        );

        return $schedules;
    }



    /**
     * Schedules activator
     * 
     */
    function mm365_schedule_activator()
    {
        $args_1 = array('123');
        $args_2 = array('321');
        $args_3 = array('421');
        $args_4 = array('721');
        $args_5 = array('921');
        $args_6 = array('322');
        $args_7 = array('8922');
        $args_8 = array('371');
        $args_22471 = array('22471');
        $args_28971 = array('28971');

        //Matches pending Notification for admin
        if (
            (!($var = wp_next_scheduled('mm365_event_pendingmatch_notification')))
            || ((($var + (1440 * MINUTE_IN_SECONDS)) < time())
                && wp_clear_scheduled_hook('mm365_event_pendingmatch_notification') >= 0)
        ) {
            wp_schedule_event(strtotime('today 14:00:00'), '1440min', 'mm365_event_pendingmatch_notification');
        }

        // Notify user about certificate expiry
        if ((!($var = wp_next_scheduled('mm365_event_certificate_expiry_notification'))) || ((($var + (1440 * MINUTE_IN_SECONDS)) < time()) && wp_clear_scheduled_hook('mm365_event_certificate_expiry_notification') >= 0)) {
            wp_schedule_event(time(), 'four_times_daily', 'mm365_event_certificate_expiry_notification');
        }

        //Put to expiry
        if ((!($var = wp_next_scheduled('mm365_event_certificate_put_to_expiry', $args_3))) || ((($var + (1440 * MINUTE_IN_SECONDS)) < time()) && wp_clear_scheduled_hook('mm365_event_certificate_put_to_expiry', $args_3) >= 0)) {
            wp_schedule_event(time(), 'daily', 'mm365_event_certificate_put_to_expiry', $args_3);
        }

        //Bulk notice
        if ((!($var = wp_next_scheduled('mm365_event_certificate_notify_expiry', $args_4))) || ((($var + (1440 * MINUTE_IN_SECONDS)) < time()) && wp_clear_scheduled_hook('mm365_event_certificate_notify_expiry', $args_4) >= 0)) {
            wp_schedule_event(time(), 'daily', 'mm365_event_certificate_notify_expiry', $args_4);
        }

        //Total count of pending certificates
        if ((!($var = wp_next_scheduled('mm365_event_admin_notify_pending_ceritifcates', $args_5))) || ((($var + (10080 * MINUTE_IN_SECONDS)) < time()) && wp_clear_scheduled_hook('mm365_event_admin_notify_pending_ceritifcates', $args_5) >= 0)) {
            wp_schedule_event(strtotime('today 15:00:00'), 'weekly', 'mm365_event_admin_notify_pending_ceritifcates', $args_5);
        }

        //Consolidated weekly report
        if ((!($var = wp_next_scheduled('mm365_event_sa_consolidated_report_mail', $args_6))) || ((($var + (10080 * MINUTE_IN_SECONDS)) < time()) && wp_clear_scheduled_hook('mm365_event_sa_consolidated_report_mail', $args_6) >= 0)) {
            wp_schedule_event(strtotime('today 22:00:00'), 'weekly', 'mm365_event_sa_consolidated_report_mail', $args_6);
        }


        //Notify council managers about the count of companies regd
        if ((!($var = wp_next_scheduled('mm365_event_cm_companiescount_mail', $args_7))) || ((($var + (1440 * MINUTE_IN_SECONDS)) < time()) && wp_clear_scheduled_hook('mm365_event_cm_companiescount_mail', $args_7) >= 0)) {
            wp_schedule_event(strtotime('today 22:00:00'), 'daily', 'mm365_event_cm_companiescount_mail', $args_7);
        }


        //Notify users about closing the match request
        if ((!($var = wp_next_scheduled('mm365_event_matchrequestclosure_mail', $args_8))) || ((($var + (43200 * MINUTE_IN_SECONDS)) < time()) && wp_clear_scheduled_hook('mm365_event_matchrequestclosure_mail', $args_8) >= 0)) {
            wp_schedule_event(strtotime('today 23:00:00'), 'once_a_month', 'mm365_event_matchrequestclosure_mail', $args_8);
        }

        //Notify 
        $args_22471 = array('22471');
        if ((!($var = wp_next_scheduled('mm365_event_notify_subscription_expiry_mail', $args_22471))) || ((($var + (720 * MINUTE_IN_SECONDS)) < time()) && wp_clear_scheduled_hook('mm365_event_notify_subscription_expiry_mail', $args_22471) >= 0)) {
            wp_schedule_event(time(), 'twicedaily', 'mm365_event_notify_subscription_expiry_mail', $args_22471);
        }

        //End subscription
        $args_28971 = array('28971');
        if ((!($var = wp_next_scheduled('mm365_event_put_subscription_to_expiry', $args_28971))) || ((($var + (720 * MINUTE_IN_SECONDS)) < time()) && wp_clear_scheduled_hook('mm365_event_put_subscription_to_expiry', $args_28971) >= 0)) {
            wp_schedule_event(time(), 'twicedaily', 'mm365_event_put_subscription_to_expiry', $args_28971);
        }

        return $var;
    }

}
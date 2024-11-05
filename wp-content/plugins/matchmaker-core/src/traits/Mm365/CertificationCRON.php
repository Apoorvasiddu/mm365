<?php
namespace Mm365;

trait CertificationCRON
{

    use CertificateAddon;

    /**
     * Fire expiring notices
     * 
     */
    function mm365_certificate_expiry_notification($args)
    {

        $this->notify_expiring_certificates('90');
        $this->notify_expiring_certificates('60');
        $this->notify_expiring_certificates('30');
        $this->notify_expiring_certificates('0');

    }


    /**
     * Chnaging status of certificate to
     *
     */
    function mm365_certificate_put_to_expiry($args)
    {
        $this->put_to_expire();
    }

    /**
     * 
     * 
     */
    function mm365_certificate_notify_expiry($args)
    {

        $this->notify_expiry();
    }

    /**
     * 
     *
     */
    function mm365_admin_notify_pending_ceritifcates($args_5)
    {
        $this->admin_notify_pending_ceritifcates();
    }

}
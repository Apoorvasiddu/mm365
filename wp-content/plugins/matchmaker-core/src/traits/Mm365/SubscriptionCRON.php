<?php
namespace Mm365;


trait SubscriptionCRON
{

    use SubscriptionAddon;
    
    /**
     * Notify before expiry
     * 
     * 
     */
    function notify_subscription_expiry(){

        $this->notify_expiring_subscriptions('90');
        $this->notify_expiring_subscriptions('60');
        $this->notify_expiring_subscriptions('30');
        $this->notify_expiring_subscriptions('0');
    }
    /**
     * End subscription once period is over
     * 
     * 
     */

    function put_subscription_to_expiry(){
      $this->deactivate_subscription();
    }


    
}
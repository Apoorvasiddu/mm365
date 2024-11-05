<?php
namespace Mm365;

/**
 * All the supporting functions for companies
 * 
 * 
 */

trait ReusableMethods
{

    /**
     * Since 3.0.0
     * @param array $var
     * Removes empty elements from an array
     */
    function purge_empty($var){
        return ($var !== NULL && $var !== FALSE && $var !== "");
    }




}
<?php
namespace Mm365;

/**
 * All the supporting functions for Admin
 * Super admin
 * Council managers
 * 
 */

trait AdminAddons
{

    /**
     * Method takes count of posts based various parameters given
     * @param string $post_type
     * @param string $period week,month, days
     * @param string $meta_key 
     * @param string $meta_value
     * @param string $column 
     * @param int $council_id
     * @param string $counci_key
     * @return int
     */

    function mm365_postcounts_timeperiod($post_type, $period = 'week', $meta_key = null, $meta_value = null, $column = 'post_modified', $council_id = NULL, $council_key = NULL)
    {

        if ($meta_key != '' and $meta_value != '') {
            $meta_search = array('key' => $meta_key, 'value' => $meta_value, 'compare' => '=');
        } else {
            $meta_search = NULL;
        }

        if ($council_id != NULL and $council_key != NULL) {
            $restrict_council = array('key' => $council_key, 'value' => $council_id, 'compare' => '=');
        } else
            $restrict_council = NULL;

        $week_query_args = array(
            'posts_per_page' => -1,
            // No limit
            'fields' => 'ids',
            // Reduce memory footprint
            'post_type' => $post_type,
            'post_status' => array('publish'),
            'date_query' => array(
                array(
                    'column' => $column,
                    'after' => '1 ' . $period . ' ago'
                )
            ),
            'meta_query' => array(
                $meta_search,
                $restrict_council
            )


        );

        $week_query = new \WP_Query($week_query_args);
        return $week_query->found_posts;
    }


    /**
     * @param string $period
     * @param string $meta_key
     * @param string $meta_value
     * @param int $council_id
     * @param string $council_key 
     * @return int
     */

    function mm365_find_matchrequests_between($period, $meta_key = NULL, $meta_value = null, $council_id = NULL, $council_key = NULL)
    {

        if ($meta_key != '' and $meta_value != '') {
            $meta_search = array('key' => $meta_key, 'value' => $meta_value, 'compare' => '=');
        } else {
            $meta_search = '';
        }

        if ($council_id != '' and $council_key != '') {
            $restrict_council = array('key' => $council_key, 'value' => $council_id, 'compare' => '=');
        } else
            $restrict_council = NULL;

        $end = date('Y-m-d');
        $start = date('Y-m-d', strtotime("-1 $period"));

        $query_args = array(
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_type' => 'mm365_matchrequests',
            'post_status' => array('publish'),
            'meta_query' => array(
                array(
                    'key' => 'mm365_matched_companies_last_updated_isodate',
                    'value' => array($start, $end),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ),
                $meta_search,
                $restrict_council,
            )
        );

        $week_query = new \WP_Query($query_args);
        return $week_query->found_posts;
    }

}
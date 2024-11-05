<?php
namespace Mm365;

trait OfflineConferencesAddon
{


    /**
     * @param int $conf_id
     * 
     * 
     */
    function get_deligates_total_count($conf_id)
    {

        //get conf_application posts ids with 'conf_id' meta

        $args = array(
            'post_type' => 'mm365_confappli',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'conf_id',
                    'value' => $conf_id,
                )
            )
        );

        $loop = new \WP_Query($args);
        $total_count = 0;
        if ($loop->have_posts()):
            while ($loop->have_posts()):
                $loop->the_post();
                $total_count += get_post_meta(get_the_ID(), 'deligates_count', true);
            endwhile;
        endif;
        wp_reset_postdata();
        return $total_count;

    }


    /**
     * Drop down values - buyer companies
     * 
     * 
     */
    function get_council_buyer_companies()
    {
        $return = array();
        $user = wp_get_current_user();
        $buyer_companies = new \WP_Query(
            array(
                's' => $_GET['q'],
                'post_type' => 'mm365_companies',
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'mm365_service_type',
                        'value' => 'buyer',
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'mm365_company_council',
                        'value' => $this->get_userDC($user->ID),
                        'compare' => '=',
                    ),
                )
            )
        );
        if ($buyer_companies->have_posts()):
            while ($buyer_companies->have_posts()):
                $buyer_companies->the_post();
                $title = (mb_strlen($buyer_companies->post->post_title) > 50) ? mb_substr($buyer_companies->post->post_title, 0, 49) . '...' : $buyer_companies->post->post_title;
                $return[] = array($buyer_companies->post->ID, $title);
            endwhile;
        endif;
        echo json_encode($return);
        wp_reset_postdata();
        wp_die();
    }

    /**
     * 
     * 
     */
    function get_existing_buyers_in_conference($conf_id)
    {

        //get  id
        $conf_id = sanitize_text_field($_POST['conf_id']);
        $nonce = sanitize_text_field($_POST['nonce']);

        if (!wp_verify_nonce($nonce, 'confshared_ajax_nonce') or !is_user_logged_in()) {
            die();
        }

        if ($conf_id != NULL) {
            //Get user meta - _mm365_associated_buyer
            $existing_buyers = get_post_meta($conf_id, 'conf_buyers');
            $return = array();
            foreach ($existing_buyers as $buyer_id) {
                $return[] = array("id" => $buyer_id, "text" => get_the_title($buyer_id));
            }
            echo json_encode($return);
            wp_die();
        }


    }
    /**
     * Drop down council manager
     * 
     * 
     */
    function get_fellow_council_manager()
    {

        $council_managers = get_users(array('role__in' => array('council_manager')));
        $user = wp_get_current_user();
        $current_user_council = $this->get_userDC($user->ID);

        $council_managers_list = array();
        foreach ($council_managers as $key => $value) {

            //Prepare list of council managers array
            if ($this->get_userDC($value->ID) == $current_user_council) {
                $council_managers_list[$value->ID] = $value->display_name;
            }

        }

        $return = array();
        //Search in to array
        $result = preg_grep("/^" . $_GET['q'] . "/i", $council_managers_list);

        foreach ($result as $key => $value) {
            $return[] = array($key, $value);
        }

        echo json_encode($return);
        wp_die();

    }

    /**
     * 
     * 
     */
    function get_existing_councilmanagers_in_conference($conf_id)
    {

        //get  id
        $conf_id = sanitize_text_field($_POST['conf_id']);
        $nonce = sanitize_text_field($_POST['nonce']);

        if (!wp_verify_nonce($nonce, 'confshared_ajax_nonce') or !is_user_logged_in()) {
            die();
        }

        if ($conf_id != NULL) {
            //Get user meta - _mm365_associated_buyer
            $existing_councilmanagers = get_post_meta($conf_id, 'conf_council_managers');
            $return = array();
            foreach ($existing_councilmanagers as $cm_id) {
                $user = get_userdata($cm_id);
                $return[] = array("id" => $cm_id, "text" => $user->display_name);
            }
            echo json_encode($return);
            wp_die();
        }


    }

    /**
     * Get Application Status
     * 
     * 
     * 
     */
    function get_application_status($conf_id, $supplier_id)
    {

        $args = array(
            'post_type' => 'mm365_confappli',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'conf_id',
                    'value' => $conf_id,
                ),
                array(
                    'key' => 'supplier_id',
                    'value' => $supplier_id,
                )
            )
        );
        $applications_list = array();
        $loop = new \WP_Query($args);
        if ($loop->have_posts()):
            while ($loop->have_posts()):
                $loop->the_post();
                $status = get_post_meta(get_the_ID(), 'status', true);
            endwhile;
        else:
            $status = '';
        endif;
        wp_reset_postdata();
        return $status;

    }

    /**
     * 
     * 
     */
    function get_suppliers_in_conference($conf_id){

        $args = array(
            'post_type' => 'mm365_confappli',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'conf_id',
                    'value' => $conf_id,
                )
            )
        );

        $loop = new \WP_Query($args);
        if ($loop->have_posts()){
            while ($loop->have_posts()):
                $loop->the_post();
                $suppliers[] = get_post_meta(get_the_ID(), 'supplier_id', true);
            endwhile;
        }else{
            $suppliers = '';
        }

        return $suppliers;

    }
}
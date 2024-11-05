<?php

namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class HelpPageManagement
{

    function __construct(){

        //Load assets
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        //Create meeting
        add_action( 'wp_ajax_update_help_page', array( $this, 'update_help_page' ) );

    }

    /*---------------------------------------------
    * Assets
    ----------------------------------------------*/
    function assets(){

        if ( wp_register_script( 'mm365_manage_helppage',plugins_url('matchmaker-core/assets/mm365_manage_helppage.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_manage_helppage' );
            wp_localize_script( 'mm365_manage_helppage', 'manageHelpPageAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("manageHelpPage_ajax_nonce")
            ) );
          
        }   

    }

    /**--------------------------------------------
     * Update help page content
     ---------------------------------------------*/
    public function update_help_page(){

        $nonce     = $_POST['nonce'];

        $user   = wp_get_current_user();
        if (!wp_verify_nonce( $nonce, 'manageHelpPage_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        //Get contents
        $content   = $_POST['help_contents'];
        $page_id   = $_POST['page_id'];

        $data = array(
            'ID' => $page_id,
            'post_content' => $content,
        );

        remove_filter( 'content_save_pre', 'wp_filter_post_kses' );
        remove_filter( 'excerpt_save_pre', 'wp_filter_post_kses' );
        remove_filter( 'content_filtered_save_pre', 'wp_filter_post_kses' );
        

        if(wp_update_post( $data )){
            echo 'success';
        }else{
            echo 'fail';
        }
        wp_die();

    }

//Class ends here
}

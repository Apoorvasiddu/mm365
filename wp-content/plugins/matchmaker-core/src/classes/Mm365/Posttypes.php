<?php
namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Posttypes {

    function __construct(){

        add_action( 'init', array($this, 'mm365_post_types') );

        add_action( 'init', array($this, 'mm365_register_taxonomies') );
    }



    /**
     * Register all post types
     * 
     * 
     */
    function mm365_post_types() 
    {
        /*---Companies custom post ----*/
        register_post_type( 'mm365_companies',
            array(
                'labels' => array(
                    'name' => __( 'Companies' ,'mm365'),
                    'singular_name' => __( 'Company' ,'mm365'),
                    'add_new' => __( 'Add New Company' ,'mm365'),
                    'add_new_item' => __( 'Add New Company' ,'mm365'),
                    'edit' => __( 'Edit Company','mm365' ),
                    'edit_item' => __( 'Edit Company','mm365' ),
                ),
                'description' => __( 'Registred companies.','mm365' ),
                'public' => true,
                'supports' => array( 'title','thumbnail' ),
                'rewrite' => array( 'slug' => 'company', 'with_front' => false ),
                'has_archive' => true,
                'show_in_menu' => true,
                'menu_position' => 7,
                //'menu_icon' => get_template_directory_uri() . '/admin/assets/images/custom/glyphicons_155_show_thumbnails.png',
            )
        );
    
        /*---RFM custom post ----*/
        register_post_type( 'mm365_matchrequests',
            array(
                'labels' => array(
                    'name' => __( 'Match Requests' ,'mm365'),
                    'singular_name' => __( 'Match Requests' ,'mm365'),
                    'add_new' => __( 'Add New Match Request' ,'mm365'),
                    'add_new_item' => __( 'Add New Match Request' ,'mm365'),
                    'edit' => __( 'Edit Match Request','mm365' ),
                    'edit_item' => __( 'Edit Match Request','mm365' ),
                ),
                'description' => __( '','mm365' ),
                'public' => true,
                'supports' => array( 'title'),
                'rewrite' => array( 'slug' => 'matchrequest', 'with_front' => false ),
                'has_archive' => true,
                'show_in_menu' => true,
                'menu_position' => 7,
                //'menu_icon' => get_template_directory_uri() . '/admin/assets/images/custom/glyphicons_155_show_thumbnails.png',
            )
        );
    
        /*---Meeting custom post ----*/
        register_post_type( 'mm365_meetings',
            array(
                'labels' => array(
                    'name' => __( 'Meetings' ,'mm365'),
                    'singular_name' => __( 'Meetings' ,'mm365'),
                    'add_new' => __( 'Add New Meeting' ,'mm365'),
                    'add_new_item' => __( 'Add New Meeting' ,'mm365'),
                    'edit' => __( 'Edit Meeting','mm365' ),
                    'edit_item' => __( 'Edit Meeting','mm365' ),
                ),
                'description' => __( '','mm365' ),
                'public' => true,
                'supports' => array( 'title'),
                'rewrite' => array( 'slug' => 'meeting', 'with_front' => false ),
                'has_archive' => true,
                'show_in_menu' => true,
                'menu_position' => 7,
                //'menu_icon' => get_template_directory_uri() . '/admin/assets/images/custom/glyphicons_155_show_thumbnails.png',
            )
        );
    
        /*---Certification custom post ----*/
        register_post_type( 'mm365_certification',
        array(
            'labels' => array(
                'name' => __( 'MBE Certification' ,'mm365'),
                'singular_name' => __( 'MBE Certification' ,'mm365'),
                'add_new' => __( 'Add New Certificate' ,'mm365'),
                'add_new_item' => __( 'Add New Certificate' ,'mm365'),
                'edit' => __( 'Edit Certificate','mm365' ),
                'edit_item' => __( 'Edit Certificate','mm365' ),
            ),
            'description' => __( '','mm365' ),
            'public' => true,
            'supports' => array( 'title'),
            'rewrite' => array( 'slug' => 'msdc_certificate', 'with_front' => false ),
            'has_archive' => true,
            'show_in_menu' => true,
            'menu_position' => 7,
            //'menu_icon' => get_template_directory_uri() . '/admin/assets/images/custom/glyphicons_155_show_thumbnails.png',
        )
        );
    
    
        /*---MSDC Post Type ----*/
        register_post_type( 'mm365_msdc',
            array(
                'labels' => array(
                    'name' => __( 'Councils' ,'mm365'),
                    'singular_name' => __( 'Council' ,'mm365'),
                    'add_new' => __( 'Add New Council' ,'mm365'),
                    'add_new_item' => __( 'Add New Council' ,'mm365'),
                    'edit' => __( 'Edit Council','mm365' ),
                    'edit_item' => __( 'Edit Council','mm365' ),
                ),
                'description' => __( 'Registred Councils.','mm365' ),
                'public' => true,
                'supports' => array( 'title','thumbnail' ),
                'rewrite' => array( 'slug' => 'msdcpt', 'with_front' => false ),
                'has_archive' => true,
                'show_in_menu' => true,
                'menu_position' => 7,
                //'menu_icon' => get_template_directory_uri() . '/admin/assets/images/custom/glyphicons_155_show_thumbnails.png',
            )
        );
    
        /*---Companies custom post ----*/
        register_post_type( 'mm365_importlog',
            array(
                'labels' => array(
                    'name' => __( 'Import Logs' ,'mm365'),
                    'singular_name' => __( 'Import' ,'mm365'),
                    'add_new' => __( 'Add New Import' ,'mm365'),
                    'add_new_item' => __( 'Add New Import' ,'mm365'),
                    'edit' => __( 'Edit Import','mm365' ),
                    'edit_item' => __( 'Edit Import','mm365' ),
                ),
                'description' => __( '','mm365' ),
                'public' => true,
                'supports' => array( 'title','thumbnail' ),
                'rewrite' => array( 'slug' => 'mmsimportlogs', 'with_front' => false ),
                'has_archive' => true,
                'show_in_menu' => true,
                'menu_position' => 12,
                //'menu_icon' => get_template_directory_uri() . '/admin/assets/images/custom/glyphicons_155_show_thumbnails.png',
            )
        );
    
        /**Updated and Tips for users */
        register_post_type( 'mm365_updatesandtips',
            array(
                'labels' => array(
                    'name' => __( 'Updates' ,'mm365'),
                    'singular_name' => __( 'Update' ,'mm365'),
                    'add_new' => __( 'Add New Update' ,'mm365'),
                    'add_new_item' => __( 'Add New Update' ,'mm365'),
                    'edit' => __( 'Edit Update','mm365' ),
                    'edit_item' => __( 'Edit Update','mm365' ),
                ),
                'description' => __( '','mm365' ),
                'public' => true,
                'supports' => array( 'title','thumbnail' ),
                'rewrite' => array( 'slug' => 'mmsupdatesandtips', 'with_front' => false ),
                'has_archive' => true,
                'show_in_menu' => true,
                'menu_position' => 12,
                //'menu_icon' => get_template_directory_uri() . '/admin/assets/images/custom/glyphicons_155_show_thumbnails.png',
            )
        );
    
    
        /**-------------------------------------------------------
         * Conference Module
         * 
         ---------------------------------------------------------*/
        register_post_type( 'mm365_conferences',
        array(
            'labels' => array(
                'name' => __( 'Conferences' ,'mm365'),
                'singular_name' => __( 'Conference' ,'mm365'),
                'add_new' => __( 'Add New Conference' ,'mm365'),
                'add_new_item' => __( 'Add New Conference' ,'mm365'),
                'edit' => __( 'Edit Conference','mm365' ),
                'edit_item' => __( 'Edit Conference','mm365' ),
            ),
            'description' => __( '','mm365' ),
            'public' => true,
            'supports' => array( 'title','thumbnail','editor' ),
            'rewrite' => array( 'slug' => 'mm365confmoduleposts', 'with_front' => false ),
            'has_archive' => true,
            'show_in_menu' => true,
            'menu_position' => 12,
            //'menu_icon' => get_template_directory_uri() . '/admin/assets/images/custom/glyphicons_155_show_thumbnails.png',
        )
       );
    
       /**--------------------------------------------------------
        * 
        ---------------------------------------------------------*/
        register_post_type( 'mm365_confappli',
        array(
            'labels' => array(
                'name' => __( 'Conf Applications' ,'mm365'),
                'singular_name' => __( 'Applications' ,'mm365'),
                'add_new' => __( 'Add New' ,'mm365'),
                'add_new_item' => __( 'Add New' ,'mm365'),
                'edit' => __( 'Edit','mm365' ),
                'edit_item' => __( 'Edit','mm365' ),
            ),
            'description' => __( '','mm365' ),
            'public' => true,
            'supports' => array( 'title','thumbnail','editor' ),
            'rewrite' => array( 'slug' => 'mm365confapplications', 'with_front' => false ),
            'has_archive' => true,
            'show_in_menu' => true,
            'menu_position' => 12,
            //'menu_icon' => get_template_directory_uri() . '/admin/assets/images/custom/glyphicons_155_show_thumbnails.png',
        )
       );
    
    
    }


    /**
     * All Taxonomies
     * 
     * 
     */

     function mm365_register_taxonomies() {
        $taxonomies = array(
            /* Taxonomies for Companies */
            array(
                'slug'         => 'company_category',
                'single_name'  => 'Category',
                'plural_name'  => 'Categories',
                'post_type'    => 'mm365_companies',
                'rewrite'      => array( 'slug' => 'company_type' ),
            ),
        );
        foreach( $taxonomies as $taxonomy ) {
            $labels = array(
                'name' => $taxonomy['plural_name'],
                'singular_name' => $taxonomy['single_name'],
                'search_items' =>  'Search ' . $taxonomy['plural_name'],
                'all_items' => 'All ' . $taxonomy['plural_name'],
                'parent_item' => 'Parent ' . $taxonomy['single_name'],
                'parent_item_colon' => 'Parent ' . $taxonomy['single_name'] . ':',
                'edit_item' => 'Edit ' . $taxonomy['single_name'],
                'update_item' => 'Update ' . $taxonomy['single_name'],
                'add_new_item' => 'Add New ' . $taxonomy['single_name'],
                'new_item_name' => 'New ' . $taxonomy['single_name'] . ' Name',
                'menu_name' => $taxonomy['plural_name']
            );
            
            $rewrite = isset( $taxonomy['rewrite'] ) ? $taxonomy['rewrite'] : array( 'slug' => $taxonomy['slug'] );
            $hierarchical = isset( $taxonomy['hierarchical'] ) ? $taxonomy['hierarchical'] : true;
        
            register_taxonomy( $taxonomy['slug'], $taxonomy['post_type'], array(
                'hierarchical' => $hierarchical,
                'labels' => $labels,
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => $rewrite,
            ));
        }
        
    }




}
<?php
namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Themeoptions{

    function __construct(){

        add_action('cmb2_admin_init', array($this, 'mm365_register_theme_options_metabox'));

    }

    function mm365_register_theme_options_metabox() {

        /**
         * Registers options page menu item and form.
         */
        $cmb_options = new_cmb2_box( array(
            'id'           => 'mm365_option_metabox',
            'title'        => esc_html__( 'Site Options', 'mm365' ),
            'object_types' => array( 'options-page' ),
    
            /*
             * The following parameters are specific to the options-page box
             * Several of these parameters are passed along to add_menu_page()/add_submenu_page().
             */
    
            'option_key'      => 'mm365_options', // The option key and admin menu page slug.
            'icon_url'        => 'dashicons-palmtree', // Menu icon. Only applicable if 'parent_slug' is left empty.
            'menu_title'      => esc_html__( 'Highlevel Options', 'mm365' ), // Falls back to 'title' (above).
            // 'parent_slug'     => 'themes.php', // Make options page a submenu item of the themes menu.
            // 'capability'      => 'manage_options', // Cap required to view options-page.
            // 'position'        => 1, // Menu position. Only applicable if 'parent_slug' is left empty.
            // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
            // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
            // 'save_button'     => esc_html__( 'Save Theme Options', 'mm365' ), // The text for the options-page save button. Defaults to 'Save'.
        ) );
    
        /*
         * Options fields ids only need
         * to be unique within this box.
         * Prefix is not needed.
         */
        $cmb_options->add_field( array(
            'name' => 'Industry List',
            'desc' => 'This data is used in Company registartion and other forms ',
            'type' => 'title',
            'id'   => 'mm365_to_industry_list_title'
        ) );
        $blog_group_id = $cmb_options->add_field( array(
            'id'          => 'industies_group',
            'type'        => 'group',
            'repeatable'  => true,
            'options'     => array(
                'group_title'   => 'Industry {#}',
                'add_button'    => 'Add Another Industry',
                'remove_button' => 'Remove Industry',
                'closed'        => true,  // Repeater fields closed by default - neat & compact.
                'sortable'      => true,  // Allow changing the order of repeated groups.
            ),
        ) );
        $cmb_options->add_group_field( $blog_group_id, array(
            'name' => 'Title',
            'desc' => 'Enter the post title for the link text.',
            'id'   => 'title',
            'type' => 'text',
            'attributes' => array(
                'required' => true,
            ),
        ) );
        $cmb_options->add_group_field( $blog_group_id, array(
            'name' => 'Mode',
            'desc' => '',
            'id'   => 'industry_display_mode',
            'type' => 'radio',
            'default' => '1',
            'options' => array(
                "0"  => "Disabled",
                "1"  => "Enabled",
            ),
            'attributes' => array(
                'required' => true,
            ),
        ));
    
        $cmb_options->add_field( array(
            'name' => 'Services List',
            'desc' => 'This data is used in Company registartion and other forms ',
            'type' => 'title',
            'id'   => 'services_list_title'
        ) );
    
        //Services provided by companies list
        $service_group_id = $cmb_options->add_field( array(
            'id'          => 'service_group',
            'type'        => 'group',
            'repeatable'  => true,
            'options'     => array(
                'group_title'   => 'Service {#}',
                'add_button'    => 'Add Another Service',
                'remove_button' => 'Remove Service',
                'closed'        => true,  // Repeater fields closed by default - neat & compact.
                'sortable'      => true,  // Allow changing the order of repeated groups.
            ),
        ) );
        $cmb_options->add_group_field( $service_group_id, array(
            'name' => 'Service Name',
            'desc' => 'Enter the title',
            'id'   => 'title',
            'type' => 'text',
            'attributes' => array(
                'required' => true,
            ),
        ) );
        $cmb_options->add_group_field( $service_group_id, array(
            'name' => 'Mode',
            'desc' => '',
            'id'   => 'services_display_mode',
            'type' => 'radio',
            'default' => '1',
            'options' => array(
                "0"  => "Disabled",
                "1"  => "Enabled",
            ),
            'attributes' => array(
                'required' => true,
            ),
        ));
        //Certifications list
        $cmb_options->add_field( array(
            'name' => 'Certifications List',
            'desc' => 'This data is used in Company registartion and other forms ',
            'type' => 'title',
            'id'   => 'certifications_list_title'
        ) );
    
        $certification_group_id = $cmb_options->add_field( array(
            'id'          => 'certification_group',
            'type'        => 'group',
            'repeatable'  => true,
            'options'     => array(
                'group_title'   => 'Certification {#}',
                'add_button'    => 'Add Another certification',
                'remove_button' => 'Remove certification',
                'closed'        => true,  // Repeater fields closed by default - neat & compact.
                'sortable'      => true,  // Allow changing the order of repeated groups.
            ),
        ) );
        $cmb_options->add_group_field( $certification_group_id, array(
            'name' => 'Certification Name',
            'desc' => 'Enter the title.',
            'id'   => 'title',
            'type' => 'text',
            'attributes' => array(
                'required' => true,
            ),
        ) );
    
        $cmb_options->add_group_field( $certification_group_id, array(
            'name' => 'Mode',
            'desc' => '',
            'id'   => 'certification_display_mode',
            'type' => 'radio',
            'default' => '1',
            'options' => array(
                "0"  => "Disabled",
                "1"  => "Enabled",
            ),
            'attributes' => array(
                'required' => true,
            ),
        ));
    
        //Minority Codes
        $cmb_options->add_field( array(
            'name' => 'Minority Category List',
            'desc' => 'This data is used in Company registartion and other forms ',
            'type' => 'title',
            'id'   => 'minoritycategory_list_title'
        ) );
    
        $minority_group_id = $cmb_options->add_field( array(
            'id'          => 'minority_group',
            'type'        => 'group',
            'repeatable'  => true,
            'options'     => array(
                'group_title'   => 'Minority Category {#}',
                'add_button'    => 'Add Another Minority Category',
                'remove_button' => 'Remove Minority Category',
                'closed'        => true,  // Repeater fields closed by default - neat & compact.
                'sortable'      => true,  // Allow changing the order of repeated groups.
            ),
        ) );
        $cmb_options->add_group_field( $minority_group_id, array(
            'name' => 'Minority Category Code',
            'desc' => 'Enter the alpha code.',
            'id'   => 'code',
            'type' => 'text_small',
            'attributes' => array(
                'required' => true,
            ),
        ) );
        $cmb_options->add_group_field( $minority_group_id, array(
            'name' => 'Minority Category - Expanded',
            'desc' => 'Enter expansion of the alpha code.',
            'id'   => 'title',
            'type' => 'text',
            'attributes' => array(
                'required' => true,
            ),
        ) );
    
        $cmb_options->add_group_field( $minority_group_id, array(
            'name' => 'Mode',
            'desc' => '',
            'id'   => 'minoritycode_display_mode',
            'type' => 'radio',
            'default' => '1',
            'options' => array(
                "0"  => "Disabled",
                "1"  => "Enabled",
            ),
            'attributes' => array(
                'required' => true,
            ),
        ));
    
            /*
         * Options fields ids only need
         * to be unique within this box.
         * Prefix is not needed.
         */
        $cmb_options->add_field( array(
            'name' => 'Match Parameters',
            'desc' => 'This data is really sensitive, if you are making changes to the values that affects the match happening from the moment you save',
            'type' => 'title',
            'id'   => 'mm365_match_parameter_values'
        ) );
    
        $cmb_options->add_field( array(
            'name' => 'Services',
            'desc' => 'Default 35% of the match Contribution is from Service match (ie User requesting for a Service and the MBE provide that service)',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_services',
            'default' => '35',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
        $cmb_options->add_field( array(
            'name' => 'Industries',
            'desc' => 'Default 10% of the match Contribution is from Service match (ie User requesting for a Service and the MBE provide that service)',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_industries',
            'default' => '10',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
        $cmb_options->add_field( array(
            'name' => 'Location',
            'desc' => 'Default 5% of the match Contribution is from Location where the of the service provider against requester',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_location',
            'default' => '5',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
    
        $cmb_options->add_field( array(
            'name' => 'Employee Count',
            'desc' => 'Default 2.5% of the match Contribution is from Location where the of the service provider against requester. This will be divided by 3 for Country, City and State',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_employeecount',
            'default' => '2.5',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
        $cmb_options->add_field( array(
            'name' => 'Company Size',
            'desc' => 'Default 2.5% of the match Contribution is from Comapny size',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_companysize',
            'default' => '2.5',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
        $cmb_options->add_field( array(
            'name' => 'NAICS Code',
            'desc' => 'Default 5% of the match Contribution is from NAICS codes matched',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_naicscode',
            'default' => '5',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
        $cmb_options->add_field( array(
            'name' => 'Certifications',
            'desc' => 'Default 2.5% of the match Contribution is ',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_certifications',
            'default' => '2.5',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
    
        $cmb_options->add_field( array(
            'name' => 'Minority Category',
            'desc' => 'Default 0% ',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_minorityclass',
            'default' => '0',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
    
        $cmb_options->add_field( array(
            'name' => 'Company Description',
            'desc' => '37.5% of the match Contribution is from Company description match against requesters match request description',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_companydescription',
            'default' => '37.5',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
    
    /**
     * v1.3 Onwards
     */
        $cmb_options->add_field( array(
            'name' => 'International services',
            'desc' => '',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_intassi',
            'default' => '0',
            'attributes' => array(
                'type' => 'number',
                'step' => '0.5'
            )
        ));
    
        $cmb_options->add_field( array(
            'name' => 'Number of Match Results',
            'desc' => '5 Results by default.',
            'type' => 'text_small',
            'id'   => 'mm365_match_parameter_resultscount',
            'default' => '',
            'attributes' => array(
                'type' => 'number',
                'step' => '1'
            )
        ));
    
    // --------------Match form parameters----------------
    
        $cmb_options->add_field( array(
            'name' => 'Match request form',
            'desc' => 'Configure number allowed keywords and character limit per keyword',
            'type' => 'title',
            'id'   => 'mr_form_title'
           ));
    
        $cmb_options->add_field( array(
            'name' => 'Number of keywords in match request form',
            'desc' => '5 keywords by default. Max:15',
            'type' => 'text_small',
            'id'   => 'mm365_mrform_keyword_count',
            'default' => '5',
            'attributes' => array(
                'type' => 'number',
                'step' => '1',
                'max'  => '15'
            )
        ));
    
        $cmb_options->add_field( array(
            'name' => 'Maximum number of charcters in each keyword',
            'desc' => '50 characters by default.',
            'type' => 'text_small',
            'id'   => 'mm365_mrform_keyword_charlimit',
            'default' => '50',
            'attributes' => array(
                'type' => 'number',
                'step' => '5',
                'max'  => '150'
            )
        ));
    
    
    
       //-------------------------Meeting types---------------------------------
       $cmb_options->add_field( array(
        'name' => 'Meeting Types',
        'desc' => 'Various meeting types',
        'type' => 'title',
        'id'   => 'meeting_types_title'
       ));
    
        $meeting_types = $cmb_options->add_field( array(
            'id'          => 'meeting_types',
            'type'        => 'group',
            'repeatable'  => true,
            'options'     => array(
                'group_title'   => 'Meeting Type {#}',
                'add_button'    => 'Add Another Meeting Type',
                'remove_button' => 'Remove Meeting Type',
                'closed'        => true,  // Repeater fields closed by default - neat & compact.
                'sortable'      => true,  // Allow changing the order of repeated groups.
            ),
        ) );
        $cmb_options->add_group_field( $meeting_types, array(
            'name' => 'Title',
            'desc' => 'Ex: Zoom Meeting',
            'id'   => 'meeting_type_title',
            'type' => 'text',
            'attributes' => array(
                'required' => true,
            ),
        ) );
        $cmb_options->add_group_field( $meeting_types, array(
            'name' => 'Logo/Icon',
            'desc' => 'Enter expansion of the alpha code.',
            'id'   => 'meeting_icon',
            'type' => 'file',
            'attributes' => array(
                'required' => true,
            ),
        ));
        $cmb_options->add_group_field( $meeting_types, array(
            'name' => 'Mode',
            'desc' => '',
            'id'   => 'meetingtype_display_mode',
            'type' => 'radio',
            'default' => '1',
            'options' => array(
                "0"  => "Disabled",
                "1"  => "Enabled",
            ),
            'attributes' => array(
                'required' => true,
            ),
        ));
       //-------------------------Looking for---------------------------------
       $cmb_options->add_field( array(
        'name' => 'International Assistance from MMSDC',
        'desc' => 'Looking for dropdown',
        'type' => 'title',
        'id'   => 'intassi_title'
       ));
    
       $intassi = $cmb_options->add_field( array(
            'id'          => 'intassi_types',
            'type'        => 'group',
            'repeatable'  => true,
            'options'     => array(
                'group_title'   => 'International Assistance {#}',
                'add_button'    => 'Add Another International Assistance',
                'remove_button' => 'Remove International Assistance',
                'closed'        => true,  // Repeater fields closed by default - neat & compact.
                'sortable'      => true,  // Allow changing the order of repeated groups.
            ),
       ));
       $cmb_options->add_group_field( $intassi, array(
        'name' => 'Title',
        'desc' => '',
        'id'   => 'intassi_type',
        'type' => 'text',
        'attributes' => array(
            'required' => true,
        ),
       ));
       $cmb_options->add_group_field( $intassi, array(
        'name' => 'Mode',
        'desc' => '',
        'id'   => 'intassi_type_mode',
        'type' => 'radio',
        'options' => array(
            "0"  => "Disabled",
            "1"  => "Enabled",
        ),
        'attributes' => array(
            'required' => true,
        ),
       ));
    
    
    
       //-------------------------Reason for closure---------------------------------
       $cmb_options->add_field( array(
        'name' => 'Reasons for closure - Completed',
        'desc' => 'Reasons for closing a match request for completed. If you are removing/editing any of the \'reasons\', older records will have that data remained',
        'type' => 'title',
        'id'   => 'reasons_title'
       ));
    
        $closure_reasons = $cmb_options->add_field( array(
            'id'          => 'closure_reasons',
            'type'        => 'group',
            'repeatable'  => true,
            'options'     => array(
                'group_title'   => 'Reason {#}',
                'add_button'    => 'Add Another reason',
                'remove_button' => 'Remove reason',
                'closed'        => true,  // Repeater fields closed by default - neat & compact.
                'sortable'      => true,  // Allow changing the order of repeated groups.
            ),
        ) );
    
        $cmb_options->add_group_field( $closure_reasons, array(
            'name' => 'Title',
            'desc' => '',
            'id'   => 'reason_text',
            'type' => 'text',
            'attributes' => array(
                'required' => true,
            ),
        ));
        $cmb_options->add_group_field( $closure_reasons, array(
            'name' => 'Mode',
            'desc' => '',
            'id'   => 'closure_completed_display_mode',
            'type' => 'radio',
            'default' => '1',
            'options' => array(
                "0"  => "Disabled",
                "1"  => "Enabled",
            ),
            'attributes' => array(
                'required' => true,
            ),
        ));
    
        $cmb_options->add_field( array(
            'name' => 'Reasons for closure - Cancelled',
            'desc' => 'Reasons for closing a match request for cancelled. If you are removing/editing any of the \'reasons\', older records will have that data remained',
            'type' => 'title',
            'id'   => 'reasons_title_cancelled'
           ));
        
            $closure_reasons_cancelled = $cmb_options->add_field( array(
                'id'          => 'closure_reasons_cancelled',
                'type'        => 'group',
                'repeatable'  => true,
                'options'     => array(
                    'group_title'   => 'Reason {#}',
                    'add_button'    => 'Add Another reason',
                    'remove_button' => 'Remove reason',
                    'closed'        => true,  // Repeater fields closed by default - neat & compact.
                    'sortable'      => true,  // Allow changing the order of repeated groups.
                ),
            ) );
        
            $cmb_options->add_group_field( $closure_reasons_cancelled, array(
                'name' => 'Title',
                'desc' => '',
                'id'   => 'reason_text',
                'type' => 'text',
                'attributes' => array(
                    'required' => true,
                ),
            ));
            $cmb_options->add_group_field( $closure_reasons_cancelled, array(
                'name' => 'Mode',
                'desc' => '',
                'id'   => 'closure_ccancelled_display_mode',
                'type' => 'radio',
                'default' => '1',
                'options' => array(
                    "0"  => "Disabled",
                    "1"  => "Enabled",
                ),
                'attributes' => array(
                    'required' => true,
                ),
            ));
    
            /**
             * ---------------------------------------
             * Subscription levels
             * ---------------------------------------
             */
            $cmb_options->add_field( array(
                'name' => 'Subscription Levels',
                'desc' => 'Various levels of subscription',
                'type' => 'title',
                'id'   => 'subscription_levels_title'
               ));
            
                $closure_reasons_cancelled = $cmb_options->add_field( array(
                    'id'          => 'subscription_levels',
                    'type'        => 'group',
                    'repeatable'  => true,
                    'options'     => array(
                        'group_title'   => 'Level {#}',
                        'add_button'    => 'Add Another Level',
                        'remove_button' => 'Remove Level',
                        'closed'        => true,  // Repeater fields closed by default - neat & compact.
                        'sortable'      => true,  // Allow changing the order of repeated groups.
                    ),
                ) );
            
                $cmb_options->add_group_field( $closure_reasons_cancelled, array(
                    'name' => 'Title',
                    'desc' => '',
                    'id'   => 'subscription_level_title',
                    'type' => 'text',
                    'attributes' => array(
                        'required' => true,
                    ),
                ));
                $cmb_options->add_group_field( $closure_reasons_cancelled, array(
                    'name' => 'Mode',
                    'desc' => '',
                    'id'   => 'subscription_level_display_mode',
                    'type' => 'radio',
                    'default' => '1',
                    'options' => array(
                        "0"  => "Disabled",
                        "1"  => "Enabled",
                    ),
                    'attributes' => array(
                        'required' => true,
                    ),
                ));
       /**
             * ---------------------------------------
             * Emails templates images
             * ---------------------------------------
             */           
  $cmb_options->add_field( array(
                'name' => 'Emails Templates',
                'desc' => ' Managing email templates Images',
                'type' => 'title',
                'id'   => 'emails_templates_images'
               ));
     
            
   $cmb_options->add_field( array(
    'name'    => 'Header Image',
    'desc'    => 'Upload an image or enter an URL.',
    'id'      => 'email_header_logo_image',
    'type'    => 'file',
    // Optional:
    'options' => array(
        'url' => false, 
    ),
    'text'    => array(
        'add_upload_file_text' => ' Upload Image' // Change upload button text. Default: "Add or Upload File"
    ),
    // query_args are passed to wp.media's library query.
    'query_args' => array(
         'type' => array(
        'image/gif',
         'image/jpeg',
         'image/png',
        ),
    ),
    'preview_size' => 'large', // Image size to use when previewing in the admin.
) );   


  $cmb_options->add_field( array(
    'name'    => 'Body Image',
    'desc'    => 'Upload an image or enter an URL.',
    'id'      => 'email_body_logo_image',
    'type'    => 'file',
    // Optional:
    'options' => array(
        'url' => false, 
    ),
    'text'    => array(
        'add_upload_file_text' => ' Upload Image' // Change upload button text. Default: "Add or Upload File"
    ),
    // query_args are passed to wp.media's library query.
    'query_args' => array(
         'type' => array(
        'image/gif',
         'image/jpeg',
         'image/png',
        ),
    ),
    'preview_size' => 'large', // Image size to use when previewing in the admin.
) );

  $cmb_options->add_field( array(
    'name'    => 'MMSDC Logo',
    'desc'    => 'Upload an image or enter an URL.',
    'id'      => 'email_mmsdc_logo_image',
    'type'    => 'file',
    // Optional:
    'options' => array(
        'url' => false, 
    ),
    'text'    => array(
        'add_upload_file_text' => ' Upload Image' // Change upload button text. Default: "Add or Upload File"
    ),
    // query_args are passed to wp.media's library query.
    'query_args' => array(
         'type' => array(
        'image/gif',
         'image/jpeg',
         'image/png',
        ),
    ),
    'preview_size' => 'large', // Image size to use when previewing in the admin.
) );          
    

    
            /**
             * ---------------------------------------
             * User Dashboard Tips & Tricks
             * ---------------------------------------
             */
    
            // $cmb_options->add_field( array(
            // 	'name' => 'User Dashboard Tips',
            // 	'desc' => 'Short paragraph messages showing on the user dashboard',
            // 	'type' => 'title',
            // 	'id'   => 'user_dash_title'
            //    ));
            
            // 	$closure_reasons_cancelled = $cmb_options->add_field( array(
            // 		'id'          => 'user_tips',
            // 		'type'        => 'group',
            // 		'repeatable'  => true,
            // 		'options'     => array(
            // 			'group_title'   => 'Tip {#}',
            // 			'add_button'    => 'Add Another Tip',
            // 			'remove_button' => 'Remove Tip',
            // 			'closed'        => true,  
            // 			'sortable'      => true, 
            // 		),
            // 	));
            
            // 	$cmb_options->add_group_field( $closure_reasons_cancelled, array(
            // 		'name' => 'Tip Content',
            // 		'desc' => '',
            // 		'id'   => 'tip_content',
            // 		'type' => 'textarea',
            // 		'attributes' => array(
            // 			'required' => true,
            // 		),
            // 	));
            // 	$cmb_options->add_group_field( $closure_reasons_cancelled, array(
            // 		'name' => 'Visible to',
            // 		'desc' => '',
            // 		'id'   => 'tip_visible_to',
            // 		'type' => 'radio',
            // 		'default' => '1',
            // 		'options' => array(
            // 			"buyer"  => "Buyer",
            // 			"supplier" => "Supplier",
            // 		),
            // 		'attributes' => array(
            // 			'required' => true,
            // 		),
            // 	));
    
            // 	$cmb_options->add_group_field( $closure_reasons_cancelled, array(
            // 		'name' => 'Mode',
            // 		'desc' => '',
            // 		'id'   => 'tip_display_mode',
            // 		'type' => 'radio',
            // 		'default' => '1',
            // 		'options' => array(
            // 			"0"  => "Disabled",
            // 			"1"  => "Enabled",
            // 		),
            // 		'attributes' => array(
            // 			'required' => true,
            // 		),
            // 	));
    
    
    }

}
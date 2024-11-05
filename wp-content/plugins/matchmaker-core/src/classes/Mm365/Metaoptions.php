<?php
namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Metaoptions{

    function __construct(){

        add_action( 'cmb2_init', array($this,'mm365_register_companies_metabox') );

        add_action( 'cmb2_init', array($this,'mm365_register_matchmaking_metabox') );

        add_action('cmb2_init', array($this, 'mm365_register_meeting_metabox'));
    }

    /**
     * Companies meta boxes
     * 
     * 
     * 
     */

    function mm365_register_companies_metabox() {

        $prefix = 'mm365_';
    
        $cmb_companies = new_cmb2_box( array(
            'id'            => $prefix . 'companiess_aj_metabox',
            'title'         => esc_html__( 'Company Data','mm365' ),
            'object_types'  => array( 'mm365_companies', ), // Post type
            'context'       => 'normal',
            'priority'      => 'low',
            'show_names'    => true, // Show field names on the left
            'show_on_cb' => 'mm365__hide_if_no_cats'
        ) );
    
    
        $cmb_companies->add_field( array(
                        'name'  => esc_html__( 'Address', 'mm365' ),
                        'desc'  =>  esc_html__( 'Heading Section Background', 'mm365' ),
                        'id'    => $prefix . 'company_address',
                        'type'  => 'textarea_small',
            ) );
    
        $cmb_companies->add_field( array(
                'name'  => esc_html__( 'Country', 'mm365' ),
                'desc'  =>  esc_html__( '', 'mm365' ),
                'id'    => $prefix . 'company_country',
                'type'  => 'text',
         ) );
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'State', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'company_state',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'City', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'company_city',
            'type'  => 'text',
        ));
    
    
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Contact Person', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'contact_person',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Company Phone', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'company_phone',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Company Email', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'company_email',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Alternate Person', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'alt_contact_person',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Alternate Phone', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'alt_phone',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Alternate Email', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'alt_email',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'ZIP Code', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'zip_code',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Website', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'website',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Service Type', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'service_type',
            'type'  => 'text',
        ));
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Minority Category', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'minority_category',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Industry', 'mm365' ),
            'desc'  =>  esc_html__( 'CSV Data', 'mm365' ),
            'id'    => $prefix . 'industry',
            'type'  => 'textarea_small',
        ));
    
        // $cmb_companies->add_field( array(
        //     'name'  => esc_html__( 'NAICS Codes', 'mm365' ),
        //     'desc'  =>  esc_html__( 'CSV Data', 'mm365' ),
        //     'id'    => $prefix . 'naics_codes',
        //     'type'  => 'textarea_small',
        // ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Number of employees', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'number_of_employees',
            'type'  => 'text',
        ));
        
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Size of Company', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'size_of_company',
            'type'  => 'text',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Certifications', 'mm365' ),
            'desc'  =>  esc_html__( 'CSV Data', 'mm365' ),
            'id'    => $prefix . 'certifications',
            'type'  => 'textarea_small',
        ));
    
    
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Services', 'mm365' ),
            'desc'  =>  esc_html__( 'CSV Data', 'mm365' ),
            'id'    => $prefix . 'services',
            'type'  => 'textarea_small',
        ));
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Main Customers', 'mm365' ),
            'desc'  =>  esc_html__( 'CSV Data', 'mm365' ),
            'id'    => $prefix . 'main_customers',
            'type'  => 'textarea_small',
        ));
    
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Company Description', 'mm365' ),
            'desc'  =>  esc_html__( 'Text', 'mm365' ),
            'id'    => $prefix . 'company_description',
            'type'  => 'textarea',
        ));
    
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'Attachment', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'company_docs',
            'type'  => 'file_list',
            'preview_size' => array( 50, 50 ), // Default: array( 50, 50 )
        ) );
    
        $cmb_companies->add_field( array(
            'name'  => esc_html__( 'MMSDC Approval rquired', 'mm365' ),
            'desc'  =>  esc_html__( 'If the feature is enabled, user can opt for approval type while requesting match', 'mm365' ),
            'id'    => $prefix . 'approval_required_feature',
            'type'  => 'select',
            'options'          => array(
                'enabled'          => __( 'Enabled', 'mm365' ),
                'disabled'         => __( 'Disabled', 'mm365' ),
            )
        ));
        
    
    }


    /**
     * Meeting meta boxes
     * Legacy
     * 
     */
    function mm365_register_meeting_metabox() {

        $prefix = 'mm365_';
    
        $cmb_meeting = new_cmb2_box( array(
            'id'            => $prefix . 'meeting_aj_metabox',
            'title'         => esc_html__( 'Meetings','mm365' ),
            'object_types'  => array( 'mm365_meetings', ), // Post type
            'context'       => 'normal',
            'priority'      => 'low',
            'show_names'    => true, // Show field names on the left
            'show_on_cb' => 'mm365__hide_if_no_cats'
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting status', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_status',
            'type'  => 'select',
            'options'          => array(
                'proposed'           => __( 'Proposed', 'mm365' ),
                'accepted'           => __( 'Accepted', 'mm365' ),
                'cancelled'          => __( 'Cancelled', 'mm365' ),
                'declined'           => __( 'Declined', 'mm365' ),
                'proposed_newtime'   => __( 'Proposed new time', 'mm365' ),
                'scheduled'          => __( 'Scheduled', 'mm365' ),
                'rescheduled'        => __( 'Re Scheduled', 'mm365' ),
            ),
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting title', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_title',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting with company', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_with_company',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting with contact person', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_with_contactperson',
            'type'  => 'text',
        ));    
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting with contact email', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_with_contactemail',
            'type'  => 'text',
        )); 
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting Date - 1', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_1',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting Date - 1 From', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_1_from',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting Date - 1 To', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_1_to',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting Date - 2', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_2',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting Date - 2 From', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_2_from',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting Date - 2 To', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_2_to',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting Date - 3', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_3',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting Date - 3 From', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_3_from',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Meeting Date - 3 To', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_3_to',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Attendee Preffered Meeting Date', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_attendee_preffered',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Attendee Preffered Meeting Date From', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_attendee_preffered_from',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Attendee Preffered Meeting Date To', 'mm365' ),
            'desc'  => esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_date_attendee_preffered_to',
            'type'  => 'text',
        ));
    
        $cmb_meeting->add_field( array(
            'name'  => esc_html__( 'Agenda', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'meeting_agenda',
            'type'  => 'textarea',
        ));
    
    }


    /**
     * 
     * 
     */
    function mm365_register_matchmaking_metabox() {

        $prefix = 'mm365_';
    
        $cmb_matchmaking = new_cmb2_box( array(
            'id'            => $prefix . 'matchmaking_aj_metabox',
            'title'         => esc_html__( 'Matchmaking Requests','mm365' ),
            'object_types'  => array( 'mm365_matchrequests', ), // Post type
            'context'       => 'normal',
            'priority'      => 'low',
            'show_names'    => true, // Show field names on the left
            'show_on_cb' => 'mm365__hide_if_no_cats'
        ) );
    
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Services looking for', 'mm365' ),
            'desc'  =>  esc_html__( 'Choses ', 'mm365' ),
            'id'    => $prefix . 'services_looking_for',
            'type'  => 'textarea_small',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Servicing Industry', 'mm365' ),
            'desc'  =>  esc_html__( 'jSON', 'mm365' ),
            'id'    => $prefix . 'services_industry',
            'type'  => 'textarea_small',
        ));
         $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Minority Category', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'mr_mbe_category',
            'type'  => 'text_small',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Details of the services ', 'mm365' ),
            'desc'  =>  esc_html__( 'Mega Text - 50% Weightage on search', 'mm365' ),
            'id'    => $prefix . 'services_details',
            'type'  => 'textarea',
        ));
    
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Country', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'service_country',
            'type'  => 'text_small',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'State', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'service_state',
            'type'  => 'text_small',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'City', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'service_city',
            'type'  => 'text_small',
        ));
    
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Location for search', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'location_for_search',
            'type'  => 'text',
        ));
    
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Certifications', 'mm365' ),
            'desc'  =>  esc_html__( 'jSON Data', 'mm365' ),
            'id'    => $prefix . 'certifications',
            'type'  => 'text',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'NAICS Codes', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'naics_codes',
            'type'  => 'text_small',
        ));
    
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Number of employees', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'number_of_employees',
            'type'  => 'text_small',
        ));
        
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Size of Company', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'size_of_company',
            'type'  => 'text_small',
        ));
    
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Requester ID', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'requester_id',
            'type'  => 'text_small',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Requester Company ID', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'requester_company_id',
            'type'  => 'text_small',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Requester Company Name', 'mm365' ),
            'desc'  =>  esc_html__( 'For search purposes', 'mm365' ),
            'id'    => $prefix . 'requester_company_name',
            'type'  => 'text',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Status', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'matchrequest_status',
            'type'  => 'select',
            'default'          => 'custom',
            'options'          => array(
                'pending'          => __( 'Pending', 'mm365' ),
                'approved'         => __( 'Approved', 'mm365' ),
                'nomatch'          => __( 'No Match', 'mm365' ),
                'auto-approved'    => __( 'Auto Approved', 'mm365' ),
                'closed'           => __( 'Closed', 'mm365' ),
            ),
        ));
    
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Approval type', 'mm365' ),
            'desc'  =>  esc_html__( 'MMSDC approval required: yes / no', 'mm365' ),
            'id'    => $prefix . 'approval_type',
            'type'  => 'text',
        ));
    
    
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Matched Companies', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'matched_companies',
            'type'  => 'text',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Matched Companies - Scores', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'matched_companies_scores',
            'type'  => 'text',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Approved by', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'matched_companies_approved_by',
            'type'  => 'text',
        ));
    
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Time of approval', 'mm365' ),
            'desc'  =>  esc_html__( '', 'mm365' ),
            'id'    => $prefix . 'matched_companies_approved_time',
            'type'  => 'text',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Last updated', 'mm365' ),
            'desc'  =>  esc_html__( 'for search related purposes only.', 'mm365' ),
            'id'    => $prefix . 'matched_companies_last_updated',
            'type'  => 'text',
        ));
        $cmb_matchmaking->add_field( array(
            'name'  => esc_html__( 'Last updated - ISO Date', 'mm365' ),
            'desc'  =>  esc_html__( 'for filtering purpose', 'mm365' ),
            'id'    => $prefix . 'matched_companies_last_updated_isodate',
            'type'  => 'text',
        ));
    
    }
    
}
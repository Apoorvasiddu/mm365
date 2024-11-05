<?php

namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AdminDashboard
{
    use AdminAddons;
    use CouncilAddons;

    function __construct(){
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        add_action('wp_ajax_nopriv_mm365_dashboard_status_cards', array( $this, 'dashboard_status_cards' ), 1 );
        add_action('wp_ajax_mm365_dashboard_status_cards', array( $this, 'dashboard_status_cards' ), 1 ); 

        add_filter( 'mm365_admin_dashboard_status_cards',  array($this, 'get_status_cards'), 10, 2 );


    }

    /**-----------------------------------
     * Assets
     -------------------------------------*/
     function assets(){
        
        wp_register_script('dashboard_filtercards', plugins_url('matchmaker-core/assets/dashboard_filter.js'),array('jquery'),false,true );
        wp_enqueue_script('dashboard_filtercards');
        $localize = array(
            'ajaxurl' => admin_url('admin-ajax.php')
        );
        wp_localize_script('dashboard_filtercards', 'dashboardFilter',$localize);
    }


    /**
     * 
     * 
     */
    function dashboard_status_cards(){

        //Period filter    
        $filter = esc_html($_POST['period']);
        
        //Filtering data for council managers
        $council_id = $_POST['council_id'];

        echo $this->get_status_cards($filter, $council_id );

        wp_die();
    }


    /**
     * @param string $filter
     * @param int $council_id
     * @return mixed
     */
    function get_status_cards($filter, $council_id = NULL){

        if($council_id != ''){
            $company_council_key = 'mm365_company_council';
            $mr_council_key = 'mm365_requester_company_council';
            $meeting_council_key = 'mm365_attendees_council_id';
            $certificate_council_key = 'mm365_submitted_council';
        
        }else{
            $company_council_key = NULL;
            $mr_council_key = NULL;
            $meeting_council_key = NULL;
            $certificate_council_key = NULL;
        }
        
        //$companies_count   = $this->mm365_postcounts_timeperiod('mm365_companies',$filter,'mm365_service_type',array("seller","buyer"),'post_date');
        $mbe_count = $this->mm365_postcounts_timeperiod('mm365_companies',$filter,'mm365_service_type','seller','post_date', $council_id, $company_council_key);
        $corp_count = $this->mm365_postcounts_timeperiod('mm365_companies',$filter,'mm365_service_type','buyer','post_date', $council_id, $company_council_key);
        $companies_count   = ($mbe_count + $corp_count);
        
        
        $mr_count =  $this->mm365_find_matchrequests_between($filter,'','', $council_id, $mr_council_key); 
        $approved_mr_count = $this->mm365_find_matchrequests_between($filter,'mm365_matchrequest_status','approved', $council_id, $mr_council_key);
        $auto_approved_mr_count = $this->mm365_find_matchrequests_between($filter,'mm365_matchrequest_status','auto-approved', $council_id, $mr_council_key); 
        
        $completed_mr_count =  $this->mm365_find_matchrequests_between($filter,'mm365_matchrequest_status','completed', $council_id, $mr_council_key); 
        //$completed_mr_count = $this->mm365_postcounts_timeperiod('mm365_matchrequests',$filter,'mm365_matchrequest_status','completed', $council_id, $mr_council_key);
        //$cancelled_mr_count = $this->mm365_postcounts_timeperiod('mm365_matchrequests',$filter,'mm365_matchrequest_status','cancelled', $council_id, $mr_council_key);
        $cancelled_mr_count =  $this->mm365_find_matchrequests_between($filter,'mm365_matchrequest_status','cancelled', $council_id, $mr_council_key); 
        
        $scheduled_count   = $this->mm365_postcounts_timeperiod('mm365_meetings',$filter,'mm365_meeting_status','scheduled','post_modified',$council_id, $meeting_council_key);
        $rescheduled_count = $this->mm365_postcounts_timeperiod('mm365_meetings',$filter,'mm365_meeting_status','rescheduled','post_modified',$council_id, $meeting_council_key);
        
        $pending_certificates_count   =   $this->mm365_postcounts_timeperiod('mm365_certification',$filter,'mm365_certificate_status','pending','post_modified', $council_id, $certificate_council_key);
        $expired_certificates_count   =   $this->mm365_postcounts_timeperiod('mm365_certification',$filter,'mm365_certificate_status','expired','post_modified', $council_id, $certificate_council_key);
        
        $dash_dwnload_ico = '<i class="fas fa-download"></i>';
        $dash_view_ico = '<i class="fas fa-eye"></i>';
        
        //$user = wp_get_current_user();
        $get_councilshortname = $this->get_council_info($council_id);
        
        ($council_id == '') ? $heading_additional = "all councils":$heading_additional = $get_councilshortname;

                $output = '
                <div class="row pbo-20">
                   <div class="col-12"><h4 class="dash-filter-heading">Statistics from '.$heading_additional.' for the last one '.$filter.'</h4></div>
                </div>
                
                <div class="row">
                        <div class="col-6 col-lg-3">
                            <div class="report-cards-card">
                                <h2>'.$companies_count.'</h2>
                                <h3>Companies registered</h3>
                                <div class="d-flex  flex-md-column icons-box ">';
                            if($companies_count > 0):
                                $output .= '<a href="'.site_url('/quick-reports?type=company&period='.$filter.'&meta=x&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="'.site_url('/view-quick-reports-companies?period='.$filter.'&meta=x&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            else:
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            endif; 
        
                $output .= '</div></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="report-cards-card">
                                <h2>'.$mbe_count.'</h2>
                                <h3>Suppliers registered</h3>
                                <div class="d-flex  flex-md-column icons-box">';
                            if($mbe_count > 0):
                                $output .= '<a href="'.site_url('/quick-reports?type=company&period='.$filter.'&meta=seller&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="'.site_url('/view-quick-reports-companies?period='.$filter.'&meta=seller&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            else:
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            endif; 
        
                $output .= '</div></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="report-cards-card">
                                <h2>'.$corp_count.'</h2>
                                <h3>Buyers registered</h3>
                                <div class="d-flex  flex-md-column icons-box">';
                           if($corp_count > 0):
                                $output .= '<a href="'.site_url('/quick-reports?type=company&period='.$filter.'&meta=buyer&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="'.site_url('/view-quick-reports-companies?period='.$filter.'&meta=buyer&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            else:
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            endif; 
        
                $output .= '</div></div>
                        </div>
                   
                        <div class="col-6 col-lg-3">
                            <div class="report-cards-card">
                                <h2>'.$mr_count.'</h2>
                                <h3>Match requests</h3>
                                <div class="d-flex  flex-md-column icons-box">';
                                if($mr_count > 0):
                                    $output .= '<a href="'.site_url('/quick-reports?type=match&period='.$filter.'&meta=x&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                    $output .= '<a href="'.site_url('/view-quick-reports-match?period='.$filter.'&meta=x&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';
                                else:
                                    $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                    $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                                endif;   
                            
                 $output .= '</div></div>
                        </div>
                        </div>
                        <div class="row">
                        <div class="col-6 col-lg-3">
                            <div class="report-cards-card">
                                <h2>'.$approved_mr_count.'</h2>
                                <h3>Approved match requests</h3>
                                <div class="d-flex  flex-md-column icons-box">';
                                if($approved_mr_count > 0):
                                   $output .= '<a href="'.site_url('/quick-reports?type=match&period='.$filter.'&meta=approved&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                   $output .= '<a href="'.site_url('/view-quick-reports-match?period='.$filter.'&meta=approved&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';
                                else:
                                    $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                    $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                                endif;   
        
                $output .= '</div></div>
                        </div>
                        <div class="col-6 col-lg-3">
                        <div class="report-cards-card">
                            <h2>'.$auto_approved_mr_count.'</h2>
                            <h3>Auto approved match requests</h3>
                            <div class="d-flex  flex-md-column icons-box">';
                            if($auto_approved_mr_count > 0):
                                $output .= '<a href="'.site_url('/quick-reports?type=match&period='.$filter.'&meta=auto-approved&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="'.site_url('/view-quick-reports-match?period='.$filter.'&meta=auto-approved&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';                    
                            else:
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            endif;   
        
                $output .= '</div></div>
                        </div>
                        <div class="col-6 col-lg-3">
                        <div class="report-cards-card">
                            <h2>'.$completed_mr_count.'</h2>
                            <h3>Completed match requests</h3>
                            <div class="d-flex  flex-md-column icons-box">';
                            if($completed_mr_count > 0):
                                $output .= '<a href="'.site_url('/quick-reports?type=match&period='.$filter.'&meta=completed&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="'.site_url('/view-quick-reports-match?period='.$filter.'&meta=completed&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';                    
                            else:
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            endif;  
        
                $output .= '</div></div>
                        </div>
                        <div class="col-6 col-lg-3">
                        <div class="report-cards-card">
                            <h2>'.$cancelled_mr_count.'</h2>
                            <h3>Cancelled match requests</h3>
                            <div class="d-flex  flex-md-column icons-box">';
                            if($cancelled_mr_count > 0):
                                $output .= '<a href="'.site_url('/quick-reports?type=match&period='.$filter.'&meta=cancelled&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="'.site_url('/view-quick-reports-match?period='.$filter.'&meta=cancelled&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';                    
                            else:
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            endif;  
                  
                $output .= '</div></div>
                        </div>
                        <div class="col-6 col-lg-3">
                        <div class="report-cards-card">
                            <h2>'.($scheduled_count + $rescheduled_count).'</h2>
                            <h3>Meetings scheduled</h3>
                            <div class="d-flex  flex-md-column icons-box">';
                            if(($scheduled_count + $rescheduled_count) > 0):
                                $output .= '<a href="'.site_url('/quick-reports?type=meetings&period='.$filter.'&meta=x&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="'.site_url('/view-quick-reports-meeting?period='.$filter.'&meta=auto-approved&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';                    
                            else:
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            endif;   
        
              
                $output .= '</div></div>
                        </div>
                        <div class="col-6 col-lg-3">
                        <div class="report-cards-card">
                            <h2>'.($pending_certificates_count).'</h2>
                            <h3>Certificates pending for verification</h3>
                            <div class="d-flex  flex-md-column icons-box">';
                            if($pending_certificates_count > 0):
                                
                                $output .= '<a href="'.site_url('/quick-reports?type=certificates&period='.$filter.'&meta=pending&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="'.site_url('/certificate-verification?stat=pending&period='.$filter.'&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';                    
                            else:
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            endif;  
        
                $output .= '</div></div>
                        </div>
                        <div class="col-6 col-lg-3">
                        <div class="report-cards-card">
                            <h2>'.($expired_certificates_count).'</h2>
                            <h3>Certificates expired</h3>
                            <div class="d-flex  flex-md-column icons-box">';
                            if($expired_certificates_count > 0):
                                $output .= '<a href="'.site_url('/quick-reports?type=certificates&period='.$filter.'&meta=expired&sacouncilfilter='.$council_id).'" class="btn-theme-download dash-report-btn">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="'.site_url('/certificate-verification?stat=expired&period='.$filter.'&sacouncilfilter='.$council_id).'" class="btn-theme-download">'.$dash_view_ico.'</a>';                    
                            else:
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_dwnload_ico.'</a>';
                                $output .= '<a href="#" class="btn-theme-download">'.$dash_view_ico.'</a>';
                            endif; 
        
        
                $output .= '</div></div>
                    </div>
                    </div>';

                return $output;    
    }


}
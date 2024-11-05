<?php
namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/*
* All methods related to Drodown Management
* CMB2 Theme options control
* Listing  
*/

class DropdownManager extends Helpers
{

    //Traits
    use CountryStateCity;
    use Mm365Files;

    function __construct(){
        
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        //Load Dropdown
        add_action( 'wp_ajax_load_dropdown', array( $this, 'load_dropdown' ) );

        //Toggle
        add_action( 'wp_ajax_toggle_mode', array( $this, 'toggle_mode' ) );

         //Add Item
         add_action( 'wp_ajax_add_item', array( $this, 'add_item' ) );

         //Filters
         add_filter('mm365_dropdown_councils' , array($this, 'council_dropdown'), 10, 2 );
         add_filter('mm365_dropdown_industries', array($this, 'dropdown_industries'),10,1);
         add_filter('mm365_dropdown_services', array($this, 'dropdown_services'),10,1);
         add_filter('mm365_dropdown_internationalassistance', array($this, 'dropdown_internationalassistance'),10,1);
         add_filter('mm365_dropdown_minoritycategory', array($this, 'dropdown_minoritycategory'),10,1);
         add_filter('mm365_dropdown_certifications', array($this, 'dropdown_certifications'),10,1);
         add_filter('mm365_dropdown_subscriptionlevels', array($this, 'dropdown_subscription_levels'),10,1);
             
         add_filter('mm365_dropdown_countries', array($this, 'countries_dropdown'),10,1);
         add_filter('mm365_dropdown_states', array($this, 'states_dropdown'),10, 4);

    }

    /**-----------------------------------
     * Assets
     -------------------------------------*/
    function assets(){
        if ( wp_register_script( 'mm365_dropdownmanager',plugins_url('matchmaker-core/assets/mm365_sa_dropdownmanager.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_dropdownmanager' );
            wp_localize_script( 'mm365_dropdownmanager', 'dropdownmanagersAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("dropdownmanager_ajax_nonce")
            ) );
        }    
    }

    /**-----------------------------------
     * Load dropdowns items
     -------------------------------------*/

    function load_dropdown(){

        //Get values
        $dropdown   = sanitize_text_field($_POST['dropdown']);
        $nonce      = sanitize_text_field($_POST['nonce']);
        
        if (!wp_verify_nonce( $nonce, 'dropdownmanager_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }
   
        $return = '<div class="col-lg-6" data-intro="List of items in selected dropdown. Use the toggle switch to change the visibility of the option">';
            //Show fields and form
            switch ($dropdown) {
                case 'code-industries':

                    $dropdown = $this->mm365_get_option('industies_group'); 
                    $drpdwn_id = 'industies_group';
                    //sort($dropdown);
                    foreach ($dropdown as $key => $value) {
                        ($value['industry_display_mode'] == 1) ? $mode = "checked" : $mode = "";
                        $return .= "<div class='settings-dropdown-item d-flex justify-content-between align-items-center'>".$value['title']."                        
                            <label class='toggle-control'>
                            <input class='toggler' data-dropdown='".$drpdwn_id."' data-recid='".$key."' type='checkbox' ".$mode.">
                            <span class='control'></span>
                            </label>
                        </div>";
                    }
                    $input_label = 'Industry';
                    $value_to = 'industries';
                break;

                case 'code-services':
                        $dropdown = $this->mm365_get_option('service_group'); 
                        $drpdwn_id = 'service_group';
                        foreach ($dropdown as $key => $value) {
                            ($value['services_display_mode'] == 1) ? $mode = "checked" : $mode = "";
                            $return .= "<div class='settings-dropdown-item d-flex justify-content-between align-items-center'>".$value['title']."                        
                            <label class='toggle-control'>
                            <input class='toggler' data-dropdown='".$drpdwn_id."' data-recid='".$key."' type='checkbox' ".$mode.">
                            <span class='control'></span>
                            </label>
                        </div>";
                        }
                        $input_label = 'Service';
                        $value_to = 'services';
                break;

                case 'code-certifications':
                        $dropdown = $this->mm365_get_option('certification_group'); 
                        $drpdwn_id = 'certification_group';
                        foreach ($dropdown as $key => $value) {
                            ($value['certification_display_mode'] == 1) ? $mode = "checked" : $mode = "";
                            $return .= "<div class='settings-dropdown-item d-flex justify-content-between align-items-center'>".$value['title']."                        
                            <label class='toggle-control'>
                            <input class='toggler' data-dropdown='".$drpdwn_id."' data-recid='".$key."' type='checkbox' ".$mode.">
                            <span class='control'></span>
                            </label>
                            </div>";
                        }
                        $input_label = 'Certificate Name';
                        $value_to = 'certifications';
                break;

                case 'code-minoritycodes':
                    $dropdown = $this->mm365_get_option('minority_group'); 
                    $drpdwn_id = 'minority_group';
                        foreach ($dropdown as $key => $value) {
                            ($value['minoritycode_display_mode'] == 1) ? $mode = "checked" : $mode = "";
                            $return .= "<div class='settings-dropdown-item d-flex justify-content-between align-items-center'>".$value['code']." - ".$value['title']."                        
                            <label class='toggle-control'>
                            <input class='toggler' data-dropdown='".$drpdwn_id."' data-recid='".$key."' type='checkbox' ".$mode.">
                            <span class='control'></span>
                            </label>
                            </div>";
                        }
                    $input_label = 'Minority Category Code - Expanded';
                    $value_to = 'minority codes';    
                break;

                case 'code-meetingtypes':
                        $dropdown = $this->mm365_get_option('meeting_types'); 
                        $drpdwn_id = 'meeting_types';
                        foreach ($dropdown as $key => $value) {
                            ($value['meetingtype_display_mode'] == 1) ? $mode = "checked" : $mode = "";
                            $return .= "<div class='settings-dropdown-item d-flex justify-content-between align-items-center'><span>".$value['meeting_type_title']."<img class='meeting-icon' src='".$value['meeting_icon']."' alt='icon'/></span>                         
                            <label class='toggle-control'>
                            <input class='toggler' data-dropdown='".$drpdwn_id."' data-recid='".$key."' type='checkbox' ".$mode.">
                            <span class='control'></span>
                            </label>
                            </div>";
                        }
                        $value_to = 'meeting types';
                        $input_label = 'Meeting Type';
                break;

                case 'code-intassi':
                        $dropdown = $this->mm365_get_option('intassi_types'); 
                        $drpdwn_id = 'intassi_types';
                        foreach ($dropdown as $key => $value) {
                            ($value['intassi_type_mode'] == 1) ? $mode = "checked" : $mode = "";
                            $return .= "<div class='settings-dropdown-item d-flex justify-content-between align-items-center'>".$value['intassi_type']."                        
                            <label class='toggle-control'>
                            <input class='toggler' data-dropdown='".$drpdwn_id."' data-recid='".$key."' type='checkbox' ".$mode.">
                            <span class='control'></span>
                            </label>
                            </div>";
                        }
                        $value_to = 'international assistance';
                        $input_label = 'International assistance';
                break;
                
                case 'code-closure_completed':
                        $dropdown = $this->mm365_get_option('closure_reasons'); 
                        $drpdwn_id = 'closure_reasons';
                        foreach ($dropdown as $key => $value) {
                            ($value['closure_completed_display_mode'] == 1) ? $mode = "checked" : $mode = "";
                            $return .= "<div class='settings-dropdown-item d-flex justify-content-between align-items-center'>".$value['reason_text']."                        
                            <label class='toggle-control'>
                            <input class='toggler' data-dropdown='".$drpdwn_id."' data-recid='".$key."' type='checkbox' ".$mode.">
                            <span class='control'></span>
                            </label>
                            </div>";
                        
                        }
                        $value_to = '`Reason for completing the match requests`';
                        $input_label = 'Reason';
                break;

                case 'code-closure_cancellend':
                        $dropdown = $this->mm365_get_option('closure_reasons_cancelled'); 
                        $drpdwn_id = 'closure_reasons_cancelled';
                        foreach ($dropdown as $key => $value) {
                            ($value['closure_ccancelled_display_mode'] == 1) ? $mode = "checked" : $mode = "";
                            $return .= "<div class='settings-dropdown-item d-flex justify-content-between align-items-center'>".$value['reason_text']."                        
                            <label class='toggle-control'>
                            <input class='toggler' data-dropdown='".$drpdwn_id."' data-recid='".$key."' type='checkbox' ".$mode.">
                            <span class='control'></span>
                            </label>
                            </div>";
                        }
                        $value_to = '`Reason for cancelling the match requests`';
                        $input_label = 'Reason';
                break;   

                case 'code-subscription_levels':
                    $dropdown = $this->mm365_get_option('subscription_levels'); 
                    $drpdwn_id = 'subscription_levels';
                    foreach ($dropdown as $key => $value) {
                        ($value['subscription_level_display_mode'] == 1) ? $mode = "checked" : $mode = "";
                        $return .= "<div class='settings-dropdown-item d-flex justify-content-between align-items-center'>".$value['subscription_level_title']."                        
                        <label class='toggle-control'>
                        <input class='toggler' data-dropdown='".$drpdwn_id."' data-recid='".$key."' type='checkbox' ".$mode.">
                        <span class='control'></span>
                        </label>
                        </div>";
                    }
                    $value_to = 'Subscription Levels';
                    $input_label = 'Level';
                break;   

           
            }

        $return .=   '</div>';
        $return .= '<div class="col-lg-6" >';
        if($dropdown != NULL){
            $return .= '<form method="post" id="mm365_dropdown_form" action="#"  data-parsley-validate enctype="multipart/form-data" data-intro="Add new option to the dropdown">
            <div class="form-row form-group" >            
            <h6>Add new value to '.$value_to.' dropdown</h6></div>';
            
            if($drpdwn_id == 'minority_group'){
                $return .= '<div class="form-row form-group">';
                $return .= '<label for="">Minority Category Code<span>*<span></label>';
                $return .= '<input required placeholder="Minority Code" class="form-control" type="text"  pattern="[a-zA-Z\s]+" minlength="2" name="minority_code">';   
                $return .= '</div>';
            }

            if($drpdwn_id == 'meeting_types'){
                $return .= '<div class="form-row form-group">
                <div class="col">
                <label for="">Upload logo<span>*</span>
                <br/><small>Drag & drop meeting type logo 
                (You can only upload .jpg or .png formats. File size should not exceed 1MB)
                </small>
                </label>
                    <br/>
                        <div class="dropzonee" id="systemsetting-dropzone" data-existing="">        
                            <div class="dz-message needsclick" for="files">Drag & drop logo.<br/>               
                                <small>(You can only upload .jpg or .png  formats. File size should not exceed 1MB)</small>
                                <div class="fallback">
                                <input class="form-control-file"   type="file" id="wp_custom_attachment" name="files" multiple />
                                </div>
                            </div>
                        </div>
                        <ul class="parsley-errors-list filled" id="validate-capability-statement" aria-hidden="false">
                        <li class="parsley-required capability-statemets-error">This value is required.</li>
                        </ul>       
                    </div>
                </div>';
            }


            $return .= '<div class="form-row form-group">';
            $return .= '<label for="">'.$input_label.'<span>*<span></label>';
            $return .= '<input required placeholder="Dropdown value" class="form-control" type="text"  pattern="[0-9a-zA-Z-\s]+" minlength="2" name="dropdown_value">';            
            $return .= '</div>  

            <div class="form-row form-group">
            <input type="hidden" name="dropdown_id" value="'.$drpdwn_id.'">
            <button id="dropdown_submit" type="submit" class="btn btn-primary">'.esc_html__('Add', 'mm365').'</button>     
            </div>    
            </form>';
        }

        $return .=  '</div>';
        echo $return;
            
        die();
        
    }

    /**
     * 
     * Toggle Dropdown
     * 
     */


    function toggle_mode(){

        $dropdown = sanitize_text_field($_POST['dropdown']);
        $record   = sanitize_text_field($_POST['recid']);
        $nonce    = sanitize_text_field($_POST['nonce']);
        
        if (!wp_verify_nonce( $nonce, 'dropdownmanager_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }


        switch ($dropdown) {
            case 'industies_group':
              $ddp_mode = 'industry_display_mode';
            break;
            case 'service_group':
                $ddp_mode = 'services_display_mode';
            case 'certification_group':
               $ddp_mode = 'certification_display_mode'; 
            break;
            case 'minority_group':
               $ddp_mode = 'minoritycode_display_mode'; 
            break;
            case 'meeting_types':
               $ddp_mode = 'meetingtype_display_mode'; 
            break;
            case 'intassi_types':
               $ddp_mode = 'intassi_type_mode'; 
            break;
            case 'closure_reasons':
               $ddp_mode = 'closure_completed_display_mode'; 
            break;
            case 'closure_reasons_cancelled':
               $ddp_mode = 'closure_ccancelled_display_mode'; 
            break;

            case 'subscription_levels':
                $ddp_mode = 'subscription_level_display_mode'; 
             break;


        }

        $get_mm365_options = maybe_unserialize(get_option('mm365_options'));

        //check current value
        $current_val = $get_mm365_options[$dropdown][$record][$ddp_mode];

        if($current_val == 0){
            $current_val = $get_mm365_options[$dropdown][$record][$ddp_mode] = 1;
            $change_btn = 'disable';
        }elseif($current_val == 1) {
            $current_val = $get_mm365_options[$dropdown][$record][$ddp_mode] = 0;
            $change_btn = 'enable';
        }

        if(update_option( 'mm365_options', $get_mm365_options)){
            $ret = $change_btn;
        } else $ret = 'failed';

        echo $ret;
        die();

    }


    /**
     * 
     * Form Action - Adding items to dropdown
     * 
     */
    function add_item(){

        $dropdown =  sanitize_text_field($_POST['dropdown_id']);
        $dropdown_value = sanitize_text_field($_POST['dropdown_value']);
        $nonce    = sanitize_text_field($_POST['nonce']);
        
        if (!wp_verify_nonce( $nonce, 'dropdownmanager_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        $get_mm365_options = maybe_unserialize(get_option('mm365_options'));


        switch ($dropdown) {
            case 'industies_group':
              $ddp_mode = 'industry_display_mode';
              array_push($get_mm365_options[$dropdown],array('title' => $dropdown_value, $ddp_mode => 1));
              $on_sucess = 'code-industries';
            break;
            case 'service_group':
                $ddp_mode = 'services_display_mode';
                array_push($get_mm365_options[$dropdown],array('title' => $dropdown_value, $ddp_mode => 1));
                $on_sucess = 'code-services';
            break;
            case 'certification_group':
               $ddp_mode = 'certification_display_mode'; 
               array_push($get_mm365_options[$dropdown],array('title' => $dropdown_value, $ddp_mode => 1));
               $on_sucess = 'code-certifications';
            break;
            case 'minority_group':
               $ddp_mode = 'minoritycode_display_mode'; 
               $code =  sanitize_text_field($_POST['minority_code']);
               array_push($get_mm365_options[$dropdown],array('code' => $code, 'title' => $dropdown_value, $ddp_mode => 1));
            //    [code] => AIF
            //         [title] => Asian Indian Female
            //         [minoritycode_display_mode] => 1
            $on_sucess = 'code-minoritycodes';
            break;
            case 'meeting_types':
               $ddp_mode = 'meetingtype_display_mode'; 
                if ( $_FILES ) { 
                    $files = $_FILES["files"];  
                    foreach ($files['name'] as $key => $value) {            
                            if ($files['name'][$key]) { 
                                $file = array( 
                                    'name' => $files['name'][$key],
                                    'type' => $files['type'][$key], 
                                    'tmp_name' => $files['tmp_name'][$key], 
                                    'error' => $files['error'][$key],
                                    'size' => $files['size'][$key]
                                ); 
                                $_FILES = array ("files" => $file); 
                                foreach ($_FILES as $file => $array) {              
                                  $newupload = $this->insert_attachment($file,0); 
                                  $attachment_url = wp_get_attachment_url($newupload);
                                }
                          } 
                        } 
                        array_push($get_mm365_options[$dropdown],array('meeting_icon_id' => $newupload, 'meeting_icon' => $attachment_url, 'meeting_type_title' => $dropdown_value, $ddp_mode => 1));
                }

            $on_sucess = 'code-meetingtypes';

            break;
            case 'intassi_types':
               $ddp_mode = 'intassi_type_mode'; 
               array_push($get_mm365_options[$dropdown],array('intassi_type' => $dropdown_value, $ddp_mode => 1));
               $on_sucess = 'code-intassi';
            break;
            case 'closure_reasons':
               $ddp_mode = 'closure_completed_display_mode'; 
               array_push($get_mm365_options[$dropdown],array('reason_text' => $dropdown_value, $ddp_mode => 1));
               $on_sucess = 'code-closure_completed';
            break;
            case 'closure_reasons_cancelled':
               $ddp_mode = 'closure_ccancelled_display_mode';
               array_push($get_mm365_options[$dropdown],array('reason_text' => $dropdown_value, $ddp_mode => 1)); 
               $on_sucess = 'code-closure_cancellend';
            break;
            case 'subscription_levels':
                $ddp_mode = 'subscription_level_display_mode';
                array_push($get_mm365_options[$dropdown],array('subscription_level_title' => $dropdown_value, $ddp_mode => 1)); 
                $on_sucess = 'code-subscription_levels';
             break;
 
        }
        
        if(update_option( 'mm365_options', $get_mm365_options)){
            $ret = $on_sucess;
        } else $ret = 'failed';

        echo $ret;

        die();
    }


    /**
     * 
     * Dropdown - Industries 
     * 
     */
    function dropdown_industries(array $current_industry){

        //There is spleeling mistake industies_group NOT industries_group - please stick on with it

        $industries_list = apply_filters('mm365_helper_get_themeoption','industies_group',NULL); 

        sort($industries_list);

        //print_r($industries_list);

        foreach ($industries_list as $key => $value) {
              if($value['industry_display_mode'] == 1){

                if(in_array($value['title'],$current_industry)){ 
                    $select = "selected"; 
                }else $select = ""; 

                echo '<option '.$select.'>'.$value['title'].'</option>';
              }
        }

    }

   /**
     * 
     * Dropdown - Services 
     * 
     */
    function dropdown_services(array $current_services){

        $service_list =  apply_filters('mm365_helper_get_themeoption','service_group',NULL);
        sort($service_list);
        foreach ($service_list as $key => $value) {
              if($value['services_display_mode'] == 1){
                if(in_array($value['title'],$current_services)){ $select = "selected"; }else $select =''; 
                echo '<option '.$select.'>'.$value['title'].'</option>';
              }
        }

    }

   /**
     * 
     * Dropdown - Minority Category 
     * 
     */

    function dropdown_minoritycategory($current_minority_category = NULL){

        $minority_category_list    = apply_filters('mm365_helper_get_themeoption','minority_group',NULL);
        foreach ($minority_category_list as $key => $value) {
            if($value['minoritycode_display_mode'] == 1){
                 echo '<option value="'.$value['code'].'" ';
                  if($value['code'] == $current_minority_category){ echo "selected"; } 
                 echo ">".$value['title']."</option>";
            }
        }

    }

     /**
     * 
     * Dropdown - International Assistance
     * 
     */
    function dropdown_internationalassistance(array $current_intassi){

        $int_assi = apply_filters('mm365_helper_get_themeoption','intassi_types',NULL);
        sort($int_assi);
        foreach ($int_assi as $key => $value) {
            if($value['intassi_type_mode'] == 1){  
                (in_array($value['intassi_type'], $current_intassi)) ? $sel = 'selected' : $sel = '';
                 echo '<option '.$sel.'>'.$value['intassi_type'].'</option>';
            }

        }

    }


    /**
     * 
     * Dropdown - Certifications
     * 
     */

    function dropdown_certifications($current_intassi = array()){

        $certification_list = apply_filters('mm365_helper_get_themeoption','certification_group',NULL);
        sort($certification_list);
        foreach ($certification_list as $key => $value) {
            if($value['certification_display_mode'] == 1){  
                (in_array($value['title'], $current_intassi)) ? $sel = 'selected' : $sel = '';
                 echo '<option '.$sel.'>'.$value['title'].'</option>';
            }

        }
        
    }

    /**
     * 
     * Drop Down - Subscription Levels
     * 
     */
    function dropdown_subscription_levels($current_subscription_level = array()){

        $subscription_levels = $this->mm365_get_option('subscription_levels'); 
        sort($subscription_levels);
        foreach ($subscription_levels as $key => $value) {
            if($value['subscription_level_display_mode'] == 1){  
                (in_array($value['subscription_level_title'], $current_subscription_level)) ? $sel = 'selected' : $sel = '';
                 echo '<option '.$sel.'>'.$value['subscription_level_title'].'</option>';
            }

        }
        
    }


    /**
     *  Moved here from Src/Helper
     * 
     * Output the list of councils as ,options
     * @param $select - for preselecting council
     * @param string $valField - id, shortname
     * 
     */
    public function council_dropdown($select = NULL, $valField = 'id'){
        $args = array(  
            'post_type' => 'mm365_msdc',
            'posts_per_page' => -1, 
            'orderby' => 'title', 
            'order' => 'asc',
            'fields' => 'ids', 
        );
        $councils = new \WP_Query( $args );  
        while ( $councils->have_posts() ) : $councils->the_post(); 
        (get_the_ID() == $select) ? $selected = 'selected': $selected = '';

        $valueAttribute = match ($valField) {
             'shortname'   => get_post_meta(get_the_ID(), 'mm365_council_shortname', true),
             default => get_the_ID()
        };

        echo '<option '.$selected.' value="'.esc_html($valueAttribute).'">'.get_post_meta(get_the_ID(), 'mm365_council_shortname', true).' - '.get_the_title(get_the_ID()).'</option>';
        endwhile;
        wp_reset_postdata();
        
    }

    /**
     * Moved here from Src/Helper
     * 
     * Output list of countries as dropdown
     * @param array  country ids - for multi selection
     * 
     */
    public function countries_dropdown( $select = array()){

        //From trait
        $country_list = $this->get_countries_list();

        foreach ($country_list as $key => $value) {  

            (in_array($value->id, $select)) ? $selected = "selected" :  $selected ='';

            echo "<option ".$selected."  value='".$value->id."' >".$value->name."</option>";
        }
    }

    /**
     * Moved here from Src/Helper
     * @param int country_id
     * @param array $select - list of countries to preselect
     * @param bool $show_any_state - Display a selection item 'ANY' as choice
     * @param bool $only_all_state - Show default 'All' option - 
     */

    // public function states_dropdown($country_id, array $select, $show_any_state = FALSE, $only_all_state = FALSE){

    //     (in_array("all_".$country_id."_states", $select)) ? $selected = "selected" :  $selected ='';

    //     if($show_any_state == TRUE) { 
    //         echo  "<option ". $selected." value='all_".$country_id."_states' >All States in ".$this->get_countryname($country_id)."</option>"; 
    //     }

    //     if($only_all_state == FALSE){

    //         $states_list = $this->get_states_list($country_id); //Trait function

    //         foreach ($states_list as $key => $value) {  

    //             (in_array($value->id, $select)) ? $selected = "selected" :  $selected ='';

    //             echo "<option ".$selected."  value='".$value->id."' >".$value->name."</option>";
    //         }
    //     }   
    // }




    /** Rest of the items are barely repeated thus added conditions on the respective template files itself 
     * meeting_types, closure_reasons, closure_reasons_cancelled
    */

    /**
     * Been used in 
     * template-company-active-edit.php - done
     * template-company-edit.php - done
     * template-company-registartion.php - done
     * template-matchrequest-edit.php - done
     * template-matchrequest-form.php -done
     * template-report-companyregistartion.php and council manager report - done
     * template-report-matchrequests.php and council manager - done
     * 
     * 
     */

    
}
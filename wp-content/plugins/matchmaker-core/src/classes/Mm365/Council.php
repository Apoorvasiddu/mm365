<?php

namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/* 
* All methods related to Managing Councils
* Add / Edit 
* Listing  
* Home Page Listing
*/

class Council
{
    use CouncilAddons;
    use Mm365Files;
    use CountryStateCity;
    use SubscriptionAddon;

    function __construct(){
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        //Add Meeting
        add_action( 'wp_ajax_add_council', array( $this, 'add_council' ) );

        //Update council
        add_action( 'wp_ajax_update_council', array( $this, 'update_council' ) );

        add_action( 'wp_ajax_show_councildata_in_home', array( $this, 'show_councildata_in_home' ) );
        add_action( 'wp_ajax_nopriv_show_councildata_in_home', array( $this, 'show_councildata_in_home' ) );


        //Filters
        add_filter('mm365_council_list',array($this, 'list' ), 10, 0);
        add_filter('mm365_council_get_info',array($this, 'get_council_info' ), 10, 2);
        add_filter('mm365_council_content_access_check',array($this, 'council_access_right' ), 10, 3);
        add_filter('mm365_council_list_by_category',[$this, 'list_by_category'],10,1);
        
    }

    /**-----------------------------------
     * Assets
     -------------------------------------*/
    function assets(){
        if ( wp_register_script( 'mm365_council_script',plugins_url('matchmaker-core/assets/mm365_sa_councils.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_council_script' );
            wp_localize_script( 'mm365_council_script', 'councilsAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("councils_ajax_nonce")
            ) );
        }  
        
        //For Home Only
        if ( wp_register_script( 'mm365_homepage',get_template_directory_uri()."/assets/javascripts/homepage.js", array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_homepage' );
            wp_localize_script( 'mm365_homepage', 'homeAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("home_ajax_nonce")
            ) );
        } 
    }

    /**-----------------------------------
     * Add Council
     -------------------------------------*/
    public function add_council(){

        $council_name = sanitize_text_field($_POST['council_name']);
        $council_short_name = sanitize_text_field($_POST['council_short_name']);
        $council_category = sanitize_text_field($_POST['council_category']);

        //Check duplicates
        $is_duplicate = $this->is_duplicate($council_name,$council_short_name);

        //Subscription status toggle
        if (isset($_POST['mbe_require_subscription'])){
            $subscrption_required = 1;
        }else $subscrption_required = 0;

        if($is_duplicate == 1){
            echo 'duplicate';
            die();
        }else{

        //Input values
        $data = array(
                    'post_type'  => 'mm365_msdc',
                    'post_title' => $council_name,
                    'post_status'=> 'publish'
        );
                        
        $post_id = wp_insert_post( $data );

            //Permission to approve match requests and set match auto approval prefrence
            if (isset($_POST['permission_mr'])){
                $permission_status = 1;
            }else $permission_status = 0;

            if (isset($_POST['preselect_mr'])){
                $preselect_stat = 1;
            }else $preselect_stat = 0;

            //Show hide in footer
            if (isset($_POST['hide_from_footer'])){
                $hide_from_footer = 1;
            }else $hide_from_footer = 0;

            $pp_image_array = array();
            if ( isset($_FILES) ) 
            { 
                $files = $_FILES["files"];  
                foreach ($files['name'] as $key => $value) 
                {            
                        if ($files['name'][$key]) { 
                            $file = array( 
                                'name' => $files['name'][$key],
                                'type' => $files['type'][$key], 
                                'tmp_name' => $files['tmp_name'][$key], 
                                'error' => $files['error'][$key],
                                'size' => $files['size'][$key]
                            ); 
                            $_FILES = array ("files" => $file); 
                            foreach ($_FILES as $file => $array) 
                            {              
                                $newupload = $this->insert_attachment($file,0); 
                                $attachment_url = wp_get_attachment_url($newupload);
                                $pp_image_array[$newupload] = $attachment_url;
                            }
                        } 
                } 
            }    


            if(is_numeric($post_id)){

                update_post_meta( $post_id, 'mm365_council_shortname', $council_short_name); 
                update_post_meta( $post_id, 'mm365_council_address', sanitize_text_field($_POST['council_address']));
                update_post_meta( $post_id, 'mm365_council_country', sanitize_text_field($_POST['council_country']));
                update_post_meta( $post_id, 'mm365_council_state', sanitize_text_field($_POST['council_state']));
                update_post_meta( $post_id, 'mm365_council_city', sanitize_text_field($_POST['council_city']));
                update_post_meta( $post_id, 'mm365_council_zip', sanitize_text_field($_POST['council_zip_code']));
                update_post_meta( $post_id, 'mm365_council_contactperson', sanitize_text_field($_POST['council_contact_person']));
                update_post_meta( $post_id, 'mm365_council_email', sanitize_text_field($_POST['council_email']));
                update_post_meta( $post_id, 'mm365_council_phone', sanitize_text_field($_POST['council_phone']));
                update_post_meta( $post_id, 'mm365_council_website', sanitize_text_field($_POST['website']));
                update_post_meta( $post_id, 'mm365_council_logo', $pp_image_array);
                update_post_meta( $post_id, 'mm365_council_description', sanitize_text_field($_POST['council_description']));
                update_post_meta( $post_id, 'mm365_council_map_link', sanitize_text_field($_POST['map_link']));
                update_post_meta( $post_id, 'mm365_council_facebook', sanitize_text_field($_POST['facebook_id']));
                update_post_meta( $post_id, 'mm365_council_instagram', sanitize_text_field($_POST['instagram_id']));
                update_post_meta( $post_id, 'mm365_council_twitter', sanitize_text_field($_POST['twitter_id']));
                update_post_meta( $post_id, 'mm365_council_linkedin', sanitize_text_field($_POST['linkedin_id']));
                update_post_meta( $post_id, 'mm365_council_youtube', sanitize_text_field($_POST['youtube_id']));
                update_post_meta( $post_id, 'mm365_council_flickr', sanitize_text_field($_POST['flickr_id']));
                update_post_meta( $post_id, 'mm365_council_added_by', sanitize_text_field($_POST['current_user']));
                update_post_meta( $post_id, 'mm365_additional_permissions', $permission_status);   
                update_post_meta( $post_id, 'mm365_council_preselect', $preselect_stat);
                update_post_meta( $post_id, 'mm365_subscription_required', $subscrption_required);
                update_post_meta( $post_id, 'mm365_council_hidefromfooter', $hide_from_footer);
                update_post_meta( $post_id, 'mm365_council_category', $council_category);
                //Update UsersWP Dropdown
                $this->update_registration_dropdown();

                $re = 'success';


            }else { $re = 'failed'; }
            echo $re;
            die();

        }

      
        
    }


    /**-----------------------------------
     * Update Council
     -------------------------------------*/
    public function update_council(){

        $council_id = sanitize_text_field($_POST['council_id']);
        $council_name = sanitize_text_field($_POST['council_name']);
        $council_short_name = sanitize_text_field($_POST['council_short_name']);
        $council_category = sanitize_text_field($_POST['council_category']);

        //Permission to approve match requests and set match auto approval prefrence
        if (isset($_POST['permission_mr'])){
            $permission_status = 1;
        }else $permission_status = 0;

         //Permission to approve match requests and set match auto approval prefrence
         if (isset($_POST['preselect_mr'])){
            $preselect_stat = 1;
        }else $preselect_stat = 0;

        //Subscription status toggle
        if (isset($_POST['mbe_require_subscription'])){
            $subscrption_required = 1;
        }else $subscrption_required = 0;

        //Show hide in footer
        if (isset($_POST['hide_from_footer'])){
            $hide_from_footer = 1;
        }else $hide_from_footer = 0;
        

         //Check duplicate name or short name
        $is_duplicate = $this->is_duplicate($council_name,$council_short_name,$council_id);
        
         if($is_duplicate == 1){
            echo 'duplicate';
            die();
         }else{
                    //Input values
                    $data = array(
                        'ID' => $council_id,
                        'post_title' => $council_name,
                    );
                    
                    $post_id = wp_update_post( $data );


                    $pp_image_array = array();
                    if ( isset($_FILES) ) 
                    { 
                        $files = $_FILES["files"];  
                        foreach ($files['name'] as $key => $value) 
                        {            
                                if ($files['name'][$key]) { 
                                    $file = array( 
                                        'name' => $files['name'][$key],
                                        'type' => $files['type'][$key], 
                                        'tmp_name' => $files['tmp_name'][$key], 
                                        'error' => $files['error'][$key],
                                        'size' => $files['size'][$key]
                                    ); 
                                    $_FILES = array ("files" => $file); 
                                    foreach ($_FILES as $file => $array) 
                                    {              
                                        $newupload = $this->insert_attachment($file,0); 
                                        $attachment_url = wp_get_attachment_url($newupload);
                                        $pp_image_array[$newupload] = $attachment_url;
                                    }
                                } 
                        } 
                    }  


                    if(is_numeric($post_id)){
                        update_post_meta( $post_id, 'mm365_council_shortname', $council_short_name); 
                        update_post_meta( $post_id, 'mm365_council_address', sanitize_text_field($_POST['council_address']));
                        update_post_meta( $post_id, 'mm365_council_country', sanitize_text_field($_POST['council_country']));
                        update_post_meta( $post_id, 'mm365_council_state', sanitize_text_field($_POST['council_state']));
                        update_post_meta( $post_id, 'mm365_council_city', sanitize_text_field($_POST['council_city']));
                        update_post_meta( $post_id, 'mm365_council_zip', sanitize_text_field($_POST['council_zip_code']));
                        update_post_meta( $post_id, 'mm365_council_contactperson', sanitize_text_field($_POST['council_contact_person']));
                        update_post_meta( $post_id, 'mm365_council_email', sanitize_text_field($_POST['council_email']));
                        update_post_meta( $post_id, 'mm365_council_phone', sanitize_text_field($_POST['council_phone']));
                        update_post_meta( $post_id, 'mm365_council_website', sanitize_text_field($_POST['website']));
                        update_post_meta( $post_id, 'mm365_edited_by', sanitize_text_field($_POST['current_user']));
                        if(!empty($pp_image_array)): update_post_meta( $post_id, 'mm365_council_logo', $pp_image_array); endif;
                        update_post_meta( $post_id, 'mm365_council_description', sanitize_text_field($_POST['council_description']));
                        update_post_meta( $post_id, 'mm365_council_map_link', sanitize_text_field($_POST['map_link']));
                        update_post_meta( $post_id, 'mm365_council_facebook', sanitize_text_field($_POST['facebook_id']));
                        update_post_meta( $post_id, 'mm365_council_instagram', sanitize_text_field($_POST['instagram_id']));
                        update_post_meta( $post_id, 'mm365_council_twitter', sanitize_text_field($_POST['twitter_id']));
                        update_post_meta( $post_id, 'mm365_council_linkedin', sanitize_text_field($_POST['linkedin_id']));
                        update_post_meta( $post_id, 'mm365_council_youtube', sanitize_text_field($_POST['youtube_id']));
                        update_post_meta( $post_id, 'mm365_council_flickr', sanitize_text_field($_POST['flickr_id']));    
                        update_post_meta( $post_id, 'mm365_additional_permissions', $permission_status);  
                        update_post_meta( $post_id, 'mm365_council_preselect', $preselect_stat);
                        update_post_meta( $post_id, 'mm365_council_hidefromfooter', $hide_from_footer);
                        update_post_meta( $post_id, 'mm365_council_category', $council_category);
                        //Update UsersWP Dropdown
                        $this->update_registration_dropdown();

                        
                        if(get_post_meta( $post_id, 'mm365_subscription_required', true ) != $subscrption_required){

                            update_post_meta( $post_id, 'mm365_subscription_required', $subscrption_required);
                    
                            //If council companies require a subscription - add meta info to all subsidiaries
                            if($subscrption_required == 1){
                                $this->enable_council_wise_subscription($post_id);
                            }else{
                                $this->disable_council_wise_subscription($post_id);
                            }

                        }


                        
                        //Adittional Permission to council managers (Add or remove)
                        if($permission_status == 1){
                           $this->councilmanager_additional_permissions($post_id);
                        }else  $this->councilmanager_additional_permissions($post_id, 'remove');

                        $re = 'success';

                    }else $re = 'failed';
                    echo $re;
                    die();
        } 

    }


  /**-----------------------------------
     * Check for duplicate entry
     -------------------------------------*/
    public function is_duplicate($full_name,$short_name,$skip_id = NULL){
        
        //Check for duplicate title
        if($skip_id != NULL){
                $name_check_args = array(
                    'post_type'     => 'mm365_msdc',
                    'post_status'   => 'publish',
                    's'             => $full_name,
                    'post__not_in'  => array($skip_id)
                );
        }else{
            $name_check_args = array(
                'post_type'     => 'mm365_msdc',
                'post_status'   => 'publish',
                's'             => $full_name
            );
        }
            
        $council_names = new \WP_Query($name_check_args);
        if($council_names->have_posts()) :
            $ret = 1;
        else:
            $ret = 0; 
        endif;


        $args = array(  
            'post_type' => 'mm365_msdc',
            'posts_per_page' => -1, 
            'orderby' => 'date', 
            'meta_query' => array(
                array(
                    'key'     => 'mm365_council_shortname',
                    'value'   => $short_name,
                    'compare' => '=',
                ),
            )    
        );

        if($skip_id != NULL){
            //Skip self
            //AND $check_title->ID == $skip_id
            $args['post__not_in'] = array($skip_id);     
            if($ret == 1 ){
                $ret = 0;
            }
        }
       
        
        //check for duplicate short name
        //Get list of Counsils, append to

        $loop = new \WP_Query( $args );  
        $count_duplicate_shortname = $loop->found_posts; 
        

        //If title or shortname is used return true
        if($ret == 1 OR $count_duplicate_shortname > 0){
            return 1;
        } else { 
            return 0;
        }

 
        wp_reset_postdata();
        
    }


    /**-----------------------------------
     * Update UsersWp Dropdown
     -------------------------------------*/
    public function update_registration_dropdown(){

        //Get list of Counsils, append to
        $args = array(  
            'post_type' => 'mm365_msdc',
            'posts_per_page' => -1, 
            'orderby' => 'title', 
            'order' => 'asc'
        );
        $loop = new \WP_Query( $args );  
        $dropdown_items = array();
        while ( $loop->have_posts() ) : $loop->the_post(); 
          array_push($dropdown_items, get_the_title().'/'.get_the_ID());
        endwhile;
        wp_reset_postdata();

        $list = implode(',', $dropdown_items);

        //format
        //<TITLE>/<ID>
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $wpdb->update( $table_name, array( 'option_values' => $list ), array( 'htmlvar_name' => 'primary_msdc' ) );
       

    }



    /**-----------------------------------
     * List Councils
     -------------------------------------*/
     public function list(){

            $args = array(  
                'post_type' => 'mm365_msdc',
                'posts_per_page' => -1, 
                'orderby' => 'date', 
            );
            $councils_list = array();
            $loop = new \WP_Query( $args );  
            while ( $loop->have_posts() ) : $loop->the_post(); 

            $location = apply_filters('mm365_helper_get_cityname',get_post_meta(get_the_ID(), 'mm365_council_city', true ),'').", ".
                        apply_filters('mm365_helper_get_statename',get_post_meta(get_the_ID(), 'mm365_council_state', true )).", ".
                        apply_filters('mm365_helper_get_countryname',get_post_meta(get_the_ID(), 'mm365_council_country', true ));
                         
            array_push($councils_list, 
                        array(
                            "ID"       => get_the_ID(),
                            "name"     => get_the_title(),
                            "location" => $location,
                            "contact"  => get_post_meta( get_the_ID(), 'mm365_council_contactperson', TRUE),
                            "modified" => get_the_modified_time("m/d/Y h:i A"),
                            "additional_permission" => get_post_meta( get_the_ID(), 'mm365_additional_permissions', TRUE),
                        )
            );
            endwhile;
            wp_reset_postdata();
            return $councils_list;
     }


    /**-----------------------------------
     * Council List Councils
     -------------------------------------*/
     public function list_by_category($category = 'founding'){

        $args = array(  
            'post_type' => 'mm365_msdc',
            'posts_per_page' => -1, 
            'orderby' => 'date', 
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key'     => 'mm365_council_category',
                    'value'   => $category,
                    'compare' => '=',
                ),
            )  
        );
        $councils_list = array();
        $loop = new \WP_Query( $args );  
        while ( $loop->have_posts() ) : $loop->the_post(); 
            $councils_list[get_the_ID()] = get_post_meta(get_the_ID(), 'mm365_council_shortname', true);
        endwhile;
        wp_reset_postdata();
        return $councils_list;
    }

    /**-----------------------------------
     * Show Council Data
     -------------------------------------*/
     public function show_councildata_in_home(){


        //Get council ID
        $council_id = sanitize_text_field( $_REQUEST['council_id'] );
        //Details
        $facebook = get_post_meta($council_id, 'mm365_council_facebook',TRUE);
        $instagram = get_post_meta($council_id, 'mm365_council_instagram',TRUE);
        $twitter = get_post_meta($council_id, 'mm365_council_twitter',TRUE);
        $linkedin = get_post_meta($council_id, 'mm365_council_linkedin',TRUE);
        $youtube = get_post_meta($council_id, 'mm365_council_youtube',TRUE);
        $flickr = get_post_meta($council_id, 'mm365_council_flickr',TRUE);
  
        $email = get_post_meta($council_id, 'mm365_council_email',TRUE);
        $phone = get_post_meta($council_id, 'mm365_council_phone',TRUE);
  
        $logo = get_post_meta($council_id, 'mm365_council_logo',TRUE);
        if(!empty($logo)){
              foreach($logo as $key => $value){
                  $path_to_file = $value;
              }
        }else{ $path_to_file = ''; }
        //Output
        ?>


                   <div class="row">
                        <div class="col-md-4 text-center text-md-right">
                            <!-- logo here -->
                            <img src="<?php echo esc_url($path_to_file); ?>" height="135px" alt=""/>
                        </div>
                        <div class="col-md-7">
                            <!-- content -->
                            <div class="council-info">
                              <div class="pto-20 pbo-20">
                                <h3><?php echo get_the_title($council_id); ?></h3>
                                <p><?php echo get_post_meta($council_id, 'mm365_council_description', TRUE); ?></p>
                              </div>
                              <div class="pbo-20">
                                <h3>Stay Connected</h3>
                                <div class="w-full">
                                    <ul class="social-links">
                                        <?php if($facebook !=''): ?><li><a href="https://facebook.com/<?php echo esc_html($facebook);?>" target="_blank"><img src="<?php echo get_template_directory_uri()?>/assets/images/fb.svg" alt=""></a></li><?php endif; ?>
                                        <?php if($twitter !=''): ?><li><a href="https://twitter.com/<?php echo esc_html($twitter);?>" target="_blank"><img src="<?php echo get_template_directory_uri()?>/assets/images/twittr.svg" alt=""></a></li><?php endif; ?>
                                        <?php if($linkedin !=''): ?><li><a href="https://www.linkedin.com/<?php echo esc_html($linkedin);?>" target="_blank"><img src="<?php echo get_template_directory_uri()?>/assets/images/Linkin.svg" alt=""></a></li><?php endif; ?>
                                        <?php if($youtube !=''): ?><li><a href="https://www.youtube.com/<?php echo esc_html($youtube);?>" target="_blank"><img src="<?php echo get_template_directory_uri()?>/assets/images/youtub.svg" alt=""></a></li><?php endif; ?>
                                        <?php if($flickr !=''): ?><li><a href="https://www.flickr.com/<?php echo esc_html($flickr);?>" target="_blank"><img src="<?php echo get_template_directory_uri()?>/assets/images/flicker.svg" alt=""></a></li><?php endif; ?>
                                        <?php if($instagram !=''): ?><li><a href="https://instagram.com/<?php echo esc_html($instagram);?>" target="_blank"><img src="<?php echo get_template_directory_uri()?>/assets/images/insta.svg" alt=""></a></li><?php endif; ?>
                                    </ul>                     
                                </div>
                              </div>  
                              <div class="pto-20 pbo-20">
                              <h3>Contact Us</h3>
                                <p>
                                    <?php 
                                    echo get_post_meta($council_id, 'mm365_council_address',TRUE)."<br/>"; 
                                    echo $this->get_cityname(get_post_meta($council_id, 'mm365_council_city',TRUE))." ".$this->get_statename(get_post_meta($council_id, 'mm365_council_state',TRUE));
                                    echo "<br/>".get_post_meta($council_id, 'mm365_council_zip',TRUE);
                                    $map_link = get_post_meta($council_id, 'mm365_council_map_link',TRUE);
                                    $website = get_post_meta($council_id, 'mm365_council_website',TRUE);
                                    ?>
                                    </p>
                                    <p><i class="fas fa-phone-volume"></i>&nbsp;&nbsp;&nbsp;<a href="tel:<?php echo esc_html($phone);?>"><?php echo esc_html($phone);?></a><br/>
                                    <i class="fas fa-envelope"></i>&nbsp;&nbsp;<a href="mailto:<?php echo esc_html($email);?>"><?php echo esc_html($email);?></a><br/>
                                    <?php if($website != ''): ?><i class="fas fa-globe"></i>&nbsp;&nbsp;<a target="_blank" href="<?php echo esc_html($website);?>"><?php echo esc_html($website);?></a><br/><?php endif; ?>
                                    <?php if($map_link != ''): ?><i class="fas fa-map-marker-alt"></i>&nbsp;&nbsp;<a target="_blank" href="<?php echo esc_html($map_link);?>">Map</a><?php endif; ?>
                                 </p>
                              </div>


                            </div>
                        </div>
                    </div>

      <?php
      die();
     }




}
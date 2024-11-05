<?php
namespace Mm365;

/**
 * All the supporting functions for Council
 * get_council_info
 * 
 * 
 */

trait CouncilAddons
{

     /**
      * @param int $council_id
      * @param string $return 
      * @return string
      */
      public function get_council_info($council_id, $return = 'shortname'){
        switch ($return) {
            case 'name':
                $ret = get_the_title($council_id);
                break;
            case 'shortname':
                $ret = get_post_meta($council_id, 'mm365_council_shortname', true);
                break;
            default:
                $ret = $council_id;
                break;
        }
        return $ret;
    }


    /**
     * Get list of all councils
     * 
     * 
     */
    public function get_councils_list(){
        $args = array(  
            'post_type' => 'mm365_msdc',
            'posts_per_page' => -1, 
            'orderby' => 'title', 
            'order' => 'asc',
        );
        $councils = new \WP_Query( $args );  
        while ( $councils->have_posts() ) : $councils->the_post(); 
          $short_name = get_post_meta(get_the_ID(), 'mm365_council_shortname', true);
          $privilege = get_post_meta(get_the_ID(), 'mm365_additional_permissions', true);
          if(!empty($privilege) AND $privilege == 1){ $ret_privileg = 'Yes'; } else $ret_privileg = 'No';
          //returns shortname, full name, privilege
          $clist[get_the_ID()] = array($short_name, get_the_title(), $ret_privileg);
        endwhile;
        wp_reset_postdata();
        return $clist;
        
    }


    /**
     * @param int $user_id
     * 
     * 
     */
    public function get_userDC($user_id = NULL){
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_usermeta';

        ($user_id == NULL) ? $find_user_id = $this->user->ID : $find_user_id = $user_id ;

        $sql = "SELECT primary_msdc FROM $table_name WHERE user_id=$find_user_id";
        $dc = $wpdb->get_row($sql);
        if(!empty($dc->primary_msdc)){
           return $dc->primary_msdc;
        }else return FALSE;

    }


    /**
     * @param int $user_id
     * @param int $council_id
     * 
     */
    public function update_user_council($user_id, $council_id){

        //Map users to Council here
        /*
        * @Dependency UsersWP Plugin
        * Find user_id in 'uwp_usermeta' table and update primary_msdc 	with id
         */
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_usermeta';
        $wpdb->update( $table_name, array( 'primary_msdc' => $council_id ), array( 'user_id' => $user_id ) );

    }


        /**
     * @param int $Council_id
     * @param string $mode
     * 
     * 
     */

     public function councilmanager_additional_permissions($council_id, $mode = 'add'){
        
        //$mm365_helper = new mm365_helpers();
        //Find all council managers from the council
        //Add or remove role based on mode
        $users  = get_users( array( 'role__in' => array( 'council_manager') ) );
        foreach ($users as $key => $value) 
        {
                //Check users council_id
                $users_council_id = $this->get_userDC($value->ID);
  
                //If user belongs to the selected council
  
                if($users_council_id == $council_id)
                {
                    //If mode is add 
                    $user = new \WP_User( $value->ID ); // create a new user object for this user
                    if($mode == 'add'){
                     $user->add_role( 'council_manager_approvers' ); 
                    }else{
                       $user->remove_role( 'council_manager_approvers' ); 
                    }   
                }          
        }
    }


    /**
     * Can council see this post
     * 
     */
    public function council_access_right($post_id, $user_council_id, $council_id_meta ){

        if($user_council_id == get_post_meta( $post_id, $council_id_meta, true)){
            return true;
        }else return false;
 
    }


    /**
     * @param int $council_id
     * return description text
     */
    function get_councilDescription($council_id){
        return get_post_meta($council_id, 'mm365_council_description', TRUE);
    }

    /**
     * @param int $council_id
     * Returns logo URL
     */
    function get_councilLogo($council_id){

        $logo = get_post_meta($council_id, 'mm365_council_logo', TRUE);

        if (!empty($logo)) {
          foreach ($logo as $key => $value) {
            $path_to_file = $value;
          }
        } else $path_to_file = '';

        return $path_to_file;
        

    }

    /**
     * @param int $council_id
     * Returns address
     */
    function get_councilAddress($council_id){
       
        return get_post_meta($council_id, 'mm365_council_address', TRUE) . "<br/>".
       apply_filters('mm365_helper_get_cityname', get_post_meta($council_id, 'mm365_council_city', TRUE)) . " ".
       apply_filters('mm365_helper_get_statename', get_post_meta($council_id, 'mm365_council_state', TRUE)).
       "<br/>" . get_post_meta($council_id, 'mm365_council_zip', TRUE);
    }

    /**
     * @param int $council_id
     * Returns web address
     */
    function get_councilWebsite($council_id){
        return get_post_meta($council_id, 'mm365_council_website', TRUE);
    }

    /**
     * @param int $council_id
     * Returns social links
     */
    function get_councilSocialLinks($council_id){

        $social = ['facebook','instagram','twitter','linkedin','youtube','flickr'];
        $socialLinks = [];
        foreach($social as $site){

           if(get_post_meta($council_id, 'mm365_council_'.$site, TRUE)) 
           $socialLinks[$site] = get_post_meta($council_id, 'mm365_council_'.$site, TRUE);
        }
        return $socialLinks;

    }

    /**
     * get council id by meta
     * 
     */
    function get_councilIdByMeta($metakey,$value){

        $args = array(
            'post_type' => 'mm365_msdc',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'fields' => 'ids',
            'meta_query' => array(
              array(
                'key' => $metakey,
                'value' => $value,
                'compare' => '=',
              ),
            )
          );
          $loop = new \WP_Query($args);
          while ($loop->have_posts()):
            $loop->the_post();
            return get_the_ID();
          endwhile;
          //wp_reset_postdata();

    }

    /**
     * Council Contact
     * 
     */
    function get_councilEmail($council_id){

        return esc_html(get_post_meta($council_id, 'mm365_council_email', TRUE));

    }

    /**
     * Council Contact
     * 
     */
    function get_councilPhone($council_id){

        return  esc_html(get_post_meta($council_id, 'mm365_council_phone', TRUE));

    }

}
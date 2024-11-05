<?php

namespace Mm365;

/**
 * Associated to City, States, Countries DB
 * 
 * 
 */

trait CountryStateCity
{


    /**
     *  Return all countries
     *  @return array
     */

    function get_countries_list()
    {
        //Refactor get id and name of country to reduce db load
        global $wpdb;
        $result = $wpdb->get_results("SELECT `id`,`name` FROM " . $wpdb->prefix . "countries");
        return $result;
    }

    /**
     * Search with name and return country ID
     * @param $keyword
     * 
     */
    function find_country($keyword)
    {
        global $wpdb;
        $result = $wpdb->get_results("SELECT `id` FROM " . $wpdb->prefix . "countries  WHERE name LIKE '%$keyword%'");
        return $result;
    }

    /** 
     * Return the name of country 
     * @param $id - country id
     * 
     */
    function get_countryname($id){
       
        if(!is_numeric($id)){
            exit;
        }
        global $wpdb;
       
        $result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."countries WHERE id=".$id);

        if($result){
            return $result->name;
        }else return NULL;
        
    }

    /**
     * Get list of states based on country id
     * @param $country_id
     * @return array 
     */
    function get_states_list($country_id, $omit = array()){
        
        if(!is_numeric($country_id)){
            exit;
        }
        global $wpdb;

        //Move this to class
        $omit = array('1402','1449','1424','1402','1398','1410','1428','1403','1438','1439','1448','1432','1413','1405','1412','1431');
        

        $result = $wpdb->get_results("SELECT *  FROM ".$wpdb->prefix."states WHERE country_id=".$country_id );
        $sorter = array_column($result, 'name');
        array_multisort($sorter, SORT_ASC, $result);
           
        //Ignore these state IDS 
        $positions = array();
        foreach ($omit as $state_id) {
              $key = array_search($state_id, array_column($result, 'id'));
              if($key != ''){
                 array_push($positions,$key);
              }
        }
        
        //Unset selected array positions 
        foreach ($positions as $id) {
              unset($result[$id]);
        }

        return $result;
   
    }

    /**
     * @param $keyword
     * @return array with state id along with name 
     * 
     */

     function find_state($keyword){
        global $wpdb;
        $result = $wpdb->get_results("SELECT `id`,`name`  FROM ".$wpdb->prefix."states  WHERE name LIKE '%$keyword%'" );
        $sorter = array_column($result, 'name');
        array_multisort($sorter, SORT_ASC, $result);
        //Ignore these state IDS - 1437
        $omit = array('1402','1449','1424','1402','1398','1410','1428','1403','1438','1439','1448','1432','1413','1405','1412','1431');
        $pos = array();
        foreach ($omit as $finder) {
           $key = array_search($finder, array_column($result, 'id'));
           if($key != ''){
              array_push($pos,$key);
           }
        }
        foreach ($pos as $id) {
           unset($result[$id]);
        }
        return $result; 
    }


    /**
     * @param $id - state id
     * @return string
     * 
     */
     function get_statename($id){
        if(!is_numeric($id)){
            exit;
        }
        global $wpdb;
      
        $result = $wpdb->get_row("SELECT `name` FROM ".$wpdb->prefix."states WHERE id=".$id);

        if($result){
            return $result->name;
        }else return NULL;
        
    }


    /**
     * Returns all cities based on state_id
     * @param $state_id
     * @return mixed
     */
    function get_cities_list($state_id){
  
        global $wpdb;
        $result = $wpdb->get_results("SELECT `id`,`name` FROM ".$wpdb->prefix."cities WHERE state_id=".$state_id);
        return $result;
      
    }


    /**
     * @param $keyword
     * @return mixed
     * 
     */
    function find_city($keyword){
        global $wpdb;
        $result = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix."cities WHERE name LIKE '%$keyword%'");
        return $result;
    }


    /**
     * @param $id - city id
     * @return string
     */
    function get_cityname($id){

        global $wpdb;
        //".$wpdb->prefix."
        $result = $wpdb->get_row("SELECT `name` FROM ".$wpdb->prefix."cities WHERE id='".$id."'");
        if($result !=''){ 
            return $result->name; 
        } else return NULL;

    }




      /**
       * @param array $countries
       * @param array $states
       * 
       */
      public function multi_countries_state_display($countries,$states){

        $ret = '';
        if(empty($countries) AND empty($states) ){
            $ret = "-";
            return $ret;
        }
        elseif(!empty($countries) OR !empty($states) )
        {
           
            //Get all states list involved
            $all_states  = $states;
            
            //Get State ids list by unsetting <all_country_states>
            foreach ($states as $key => $value) {
                if (!is_numeric($value)) {
                    unset($states[$key]);
                }
            }
    
            foreach ($all_states as $key => $value) {
                if (is_numeric($value)) {
                    unset($all_states[$key]);
                }
            }
    
            //Get states from table
            $states_array = array();
            if(!empty($states)):
                $where_in = implode(',', $states);
                if($where_in != ''){
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'states';
                    $sql = "SELECT `id`,`name`,`country_id` FROM $table_name WHERE id IN ($where_in)";
                    $states_array = $wpdb->get_results($sql);
                } else $states_array = array();
      
            endif; 
    
    
            //Loop through countries and look for states
            foreach ($countries as $country_id) {
                $state_counter = 0;
                $country_name =  $this->get_countryname($country_id);
                $ret .=  esc_html($country_name.' - ');
                $states_names = array();
                //All States
                if(!empty($all_states)){
                    foreach ($all_states as $cid){
                        $country_associated = explode("_",$cid);
                        if($country_associated[1] == $country_id){
                            $ret .=  esc_html("All states in ".$country_name);
                            $state_counter = $state_counter+1;
                        }
                    }
                }
    
                //State names
                foreach ($states_array as $key => $value) {
                    if($value->country_id == $country_id){
                        $states_names[] = $value->name;
                        $state_counter = $state_counter+1;
                    }
    
                }
    
                if(count($states_names) > 0){
                  $ret .=  esc_html(implode(", ", $states_names));
                }
    
                if($state_counter == 0){
                  $ret .=  esc_html("All states in ".$country_name);
                }
                
                $ret .=  "<br/>";
                
            }
    
            return $ret;
    
        }
    }




    /**
     * @param array $countries
     * @param array $states
     * 
     */


     public function countries_states_sorter($countries,$states){

        if(empty($countries) AND empty($states) ){
            return NULL;
        }
        elseif(!empty($countries) OR !empty($states) )
        {
            $countries_list = $countries;
            $states_list = array();
           
            //Get all states list involved
            $all_states  = $states;
            
            //Get State ids list by unsetting <all_country_states>
            foreach ($states as $key => $value) {
                if (!is_numeric($value)) {
                    unset($states[$key]);
                }
            }

            foreach ($all_states as $key => $value) {
                if (is_numeric($value)) {
                    unset($all_states[$key]);
                }
            }

            //Get states from table
            $states_array = array();
            if(!empty($states)):
                $where_in = implode(',', $states);
                if($where_in != ''){
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'states';
                    $sql = "SELECT `id`,`name`,`country_id` FROM $table_name WHERE id IN ($where_in)";
                    $states_array = $wpdb->get_results($sql);
                } else $states_array = array();
            endif; 


            //Loop through countries and look for states
            foreach ($countries as $country_id) {
                $state_counter = 0;

                //All States
                if(!empty($all_states)){
                    foreach ($all_states as $all_state_selected){
                        $states_list[] = $all_state_selected;
                    }
                }

                //State names
                foreach ($states_array as $key => $value) {
                    if($value->country_id == $country_id){
                        $states_list[] = $value->id;
                        $state_counter = $state_counter+1;
                    }

                }

               //If no state is selected add all option
                if($state_counter == 0):
                  $states_list[] =  'all_'.$country_id.'_states';
                endif;

                
            }

            return array("countries" => $countries_list, "states" => array_unique($states_list));

        }
    }

      /**
     * Moved here from Src/Helper
     * @param int country_id
     * @param array $select - list of countries to preselect
     * @param bool $show_any_state - Display a selection item 'ANY' as choice
     * @param bool $only_all_state - Show default 'All' option - 
     */

     public function states_dropdown($country_id, array $select, $show_any_state = FALSE, $only_all_state = FALSE){

        (in_array("all_".$country_id."_states", $select)) ? $selected = "selected" :  $selected ='';

        if($show_any_state == TRUE) { 
            echo  "<option ". $selected." value='all_".$country_id."_states' >All States in ".$this->get_countryname($country_id)."</option>"; 
        }

        if($only_all_state == FALSE){

            $states_list = $this->get_states_list($country_id); //Trait function

            foreach ($states_list as $key => $value) {  

                (in_array($value->id, $select)) ? $selected = "selected" :  $selected ='';

                echo "<option ".$selected."  value='".$value->id."' >".$value->name."</option>";
            }
        }   
    }

}
<?php
namespace Mm365;

/**
 * All the supporting functions for companies
 * 
 * 
 */

trait CompaniesAddon
{

   use CountryStateCity;

   /**
    * @param string $service_type
    *
    */
   function get_company_service_type($service_type)
   {

      if ($service_type == 'buyer') {
         return "Buyer";
      } elseif ($service_type == 'seller') {
         return "Supplier";
      } else {
         return "";
      }

   }


   /**
    * @param string $word
    * 
    */
   function expand_minoritycode($word)
   {

      if ($word != '') {
         //Print all codes match to array and return
         $minority_category_list = apply_filters('mm365_helper_get_themeoption', 'minority_group', NULL);
         foreach ($minority_category_list as $key => $value) {
            if ($value['code'] == $word) {
               return $value['title'];
            }
         }
      } else
         return "-";
   }



   /**
    * @param mixed $user 
    * @param string $status
    * 
    */
   public function get_user_company_id($user, $status = 'publish')
   {

      if (in_array('business_user', (array) $user->roles)) {
         $args = array(
            'author' => $user->ID,
            'post_type' => 'mm365_companies',
            'fields' => 'ids',
            'post_status' => $status,
            'posts_per_page' => 1,
            'orderby' => 'title',
         );
         $company_query = new \WP_Query($args);
         if ($company_query->found_posts > 0) {
            foreach ($company_query->posts as $company_id) {
               return $company_id;
            }
         } else
            return FALSE;

      } else
         return FALSE;

   }


   /**------------------------------------------------------------------------
* Remove HTML entities from company title for EXCEL export
--------------------------------------------------------------------------*/
   function replace_html_in_companyname($title)
   {
      $text = array("&", "&", "'", "'", "-");
      $html = array("&#038;", "&amp;", "&#8217;", "&#8216;", "&#8211;");
      //get_the_title() 
      return str_replace($html, $text, wp_filter_nohtml_kses($title));
   }


}
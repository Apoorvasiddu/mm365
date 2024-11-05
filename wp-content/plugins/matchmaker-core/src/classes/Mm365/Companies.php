<?php

namespace Mm365;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class Companies extends Helpers
{


  use CompaniesAddon;
  use Mm365Files;
  use CountryStateCity;

  public $user;

  function __construct()
  {

    $this->user = wp_get_current_user();

    add_action('wp_enqueue_scripts', array($this, 'assets'), 11);


    //AJAX
    add_action('wp_ajax_mm365_company_create', [$this, 'create'], 10, 0);
    add_action('wp_ajax_mm365_company_update', [$this, 'update'], 10, 0);
    add_action('wp_ajax_serviceable_states', [$this, 'serviceable_states']);
    add_action('wp_ajax_mm365_update_company_description',[$this,'update_company_description']);

    //Preview company info
    add_filter('mm365_company_show', array($this, 'show'), 10, 3);
    add_filter('mm365_company_preload_serviceable_states', array($this, 'preload_serviceable_states'), 10, 2);
    add_filter('mm365_company_delete', [$this, 'delete'], 10, 1);


    //Filters from Trait
    add_filter('mm365_company_get_type', array($this, 'get_company_service_type'), 10, 1);

  }


  /**
   * Assets
   * 
   * 
   */

  function assets()
  {

    wp_register_script('mm365_companies', plugins_url('matchmaker-core/assets/save_company.js'), array('jquery'), false, TRUE);
    wp_register_script('parsley', plugins_url('matchmaker-core/assets/parsley.min.js'), array('jquery'), false, TRUE);

    //(tiny.cloud account - jbridges@minoritysupplier.org)
    wp_register_script('tinymce', 'https://cdn.tiny.cloud/1/p6bk80euywie64hssqi0gwfxqnq38mhua220uavgw645g2tn/tinymce/6/tinymce.min.js', array());

    wp_enqueue_script('mm365_companies');
    wp_enqueue_script('parsley');
    wp_enqueue_script('tinymce');

    wp_localize_script('mm365_companies', 'companyAjax', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce("company_ajax_nonce")
    ));


    //Addons
    if (wp_register_script('mm365_companyaddon', plugins_url('matchmaker-core/assets/mm365_sa_companyaddons.js'), array('jquery'), false, TRUE)) {
      wp_enqueue_script('mm365_companyaddon');
      wp_localize_script('mm365_companyaddon', 'companyaddonsAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce("companyaddon_ajax_nonce")
      ));
    }

  }


  /**
   * Create
   * Create company - Adds a post to mm365_companies post type
   * 
   */
  function create()
  {


    if (!wp_verify_nonce($_REQUEST['nonce'], 'company_ajax_nonce')) {
      die();
    }

    /*
     * Prepare Image array for documents uploading (Capability Statements )
     * Accepts PDF, DOCX, DOC, JPG, PNG 
     */

    $pp_image_array = array();
    if ($_FILES) {
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
          $_FILES = array("files" => $file);
          foreach ($_FILES as $file => $array) {
            $newupload = $this->insert_attachment($file, 0);
            $attachment_url = wp_get_attachment_url($newupload);
            $pp_image_array[$newupload] = $attachment_url;
          }
        }
      }
    }




    if (isset($_POST['company_name']) && $_POST['company_name'] != '') {

      //Service type - Defines Buyer or Seller Company - buyer/supllier(seller)
      $service_type = $_POST['service_type'];

      //Capture minority classification only for supplier
      if ($service_type == 'seller') {
        $minority_category = $_POST['minority_category'];
      } else
        $minority_category = '';

      //Industries that the company belongs to
      $indstry = array();
      if (isset($_POST['industry'])) {
        $indstry = $_POST['industry'];
        if (in_array('other', $indstry)) {
          $oth_indus = explode(",", $_POST['other_industry']);
        } else
          $oth_indus = "";
      }

      //Certifications that this company achieved - preselect + input for other 
      $certification = array();
      if (isset($_POST['certifications'])) {
        $certification = $_POST['certifications'];
        if (in_array('other', $certification)) {
          $oth_cert = explode(",", $_POST['other_certification']);
        } else
          $oth_cert = "";
      }

      //Services that this company offers - preselect + input for other 
      $services = array();
      if (isset($_POST['services'])) {
        $services = $_POST['services'];
        if (in_array('other', $services)) {
          $oth_serv = explode(",", $_POST['other_services']);
        } else
          $oth_serv = "";
      }

      //Insert info array

      $post_information = array(
        'post_title' => wp_strip_all_tags($_POST['company_name']),
        'meta_input' => array(
          'mm365_company_country' => sanitize_text_field($_POST['company_country']),
          'mm365_company_address' => sanitize_text_field($_POST['company_address']),
          'mm365_company_state' => sanitize_text_field($_POST['company_state']),
          'mm365_company_city' => sanitize_text_field($_POST['company_city']),
          'mm365_zip_code' => sanitize_text_field($_POST['zip_code']),
          'mm365_contact_person' => sanitize_text_field($_POST['contact_person']),
          'mm365_company_phone_type' => sanitize_text_field($_POST['primary_phone_type']),
          'mm365_company_phone' => sanitize_text_field($_POST['phone']),
          'mm365_company_email' => sanitize_email($_POST['company_email']),
          'mm365_alt_contact_person' => sanitize_text_field($_POST['alt_contact_person']),
          'mm365_alt_phone' => sanitize_text_field($_POST['alt_phone']),
          'mm365_alt_email' => sanitize_email($_POST['alt_email']),
          'mm365_website' => sanitize_text_field($_POST['website']),
          'mm365_service_type' => $service_type,
          'mm365_minority_category' => $minority_category,
          'mm365_number_of_employees' => sanitize_text_field($_POST['number_of_employees']),
          'mm365_size_of_company' => sanitize_text_field($_POST['size_of_company']),
          'mm365_main_customers' => json_encode(array_filter($_POST['main_customers'], array($this, "purge_empty"))),
          'mm365_company_description' => wp_kses_post($_POST['company_description']),
          'mm365_company_docs' => $pp_image_array,
          'mm365_company_name' => wp_strip_all_tags($_POST['company_name'])
        ),
        'post_type' => 'mm365_companies',
        'post_status' => 'draft',
        'post_author' => $this->user->ID
      );

      $post_id = wp_insert_post($post_information);

      //Map council
      $council_id = sanitize_text_field($_POST['company_council']);
      update_post_meta($post_id, 'mm365_company_council', $council_id);

      //Update USER META 
      $this->update_user_council($this->user->ID, $council_id);

      //Loop and save NAICS
      $new_naics = array_filter($_POST['naics_codes'], array($this, "purge_empty"));
      delete_post_meta($post_id, 'mm365_naics_codes');
      foreach ($new_naics as $naic) {
        add_post_meta($post_id, 'mm365_naics_codes', $naic);
      }
      //certification
      $new_certification = array_filter($certification, array($this, "purge_empty"));
      delete_post_meta($post_id, 'mm365_certifications');
      foreach ($new_certification as $data) {
        add_post_meta($post_id, 'mm365_certifications', $data);
      }
      if (!empty($oth_cert)) {
        foreach ($oth_cert as $data) {
          if ($data != 'other')
            add_post_meta($post_id, 'mm365_certifications', $data);
        }
      }

      //Services
      $new_services = array_filter($services, array($this, "purge_empty"));
      delete_post_meta($post_id, 'mm365_services');
      foreach ($new_services as $data) {
        add_post_meta($post_id, 'mm365_services', $data);
      }
      if (!empty($oth_serv)) {
        foreach ($oth_serv as $data) {
          if ($data != 'other')
            add_post_meta($post_id, 'mm365_services', $data);
        }
      }
      //Industry
      $new_industry = array_filter($indstry, array($this, "purge_empty"));
      delete_post_meta($post_id, 'mm365_industry');
      foreach ($new_industry as $data) {
        add_post_meta($post_id, 'mm365_industry', $data);
      }
      if (!empty($oth_indus)) {
        foreach ($oth_indus as $data) {
          if ($data != 'other')
            add_post_meta($post_id, 'mm365_industry', $data);
        }
      }

      //Update international assistance from MMSDC
      if (isset($_POST['international_assistance'])):
        $int_assi = array_filter($_POST['international_assistance'], array($this, "purge_empty"));
        delete_post_meta($post_id, 'mm365_international_assistance');
        foreach ($int_assi as $data) {
          add_post_meta($post_id, 'mm365_international_assistance', $data);
        }
      endif;

      if ($service_type == 'seller') {
        //Serviceable countries and states
        $servicebale_countries = $_POST['serviceable_countries'];
        delete_post_meta($post_id, 'mm365_cmp_serviceable_countries');
        foreach ($servicebale_countries as $value) {
          add_post_meta($post_id, 'mm365_cmp_serviceable_countries', $value);
        }

        $servicebale_states = $_POST['serviceable_states'];
        delete_post_meta($post_id, 'mm365_com_serviceable_states');
        foreach ($servicebale_states as $value) {
          add_post_meta($post_id, 'mm365_cmp_serviceable_states', $value);
        }

        //Check if subscription is required from council level and then enable the status on company info
        if (get_post_meta($council_id, 'mm365_subscription_required', true) == 1) {
          add_post_meta($post_id, 'mm365_subscription_status', 'Not Subscribed');
        }

      }

      //Return show comapny
      return $this->show($post_id);

    } else {

      echo "Error";

    }

    die();


  }

  /**
   * Update
   * Update company info - Update selected post mm365_companies post type
   * 
   */
  function update()
  {

    if (!wp_verify_nonce($_REQUEST['nonce'], 'company_ajax_nonce')) {
      die();
    }

    $auth = get_post($_POST['company_id']); // gets author from post
    $authid = $auth->post_author; // gets author id for the post

    //Check if author is editing
    if ($this->user->ID != $authid) {
      die();
    }

    //Mod Switch
    if (isset($_POST['company_status']) and $_POST['company_status'] == 'active') {
      $status_mode = 'publish';
    } else {
      $status_mode = 'draft';
    }

    //Capability statement upload
    $pp_image_array = array();
    //Attached Images Maintenance
    if (isset($_POST['existing_files'])) {
      $active_attachments = get_post_meta($_POST['company_id'], 'mm365_company_docs', true);
      $attachments_to_keep = explode(",", $_POST['existing_files']);
      foreach ($active_attachments as $key => $value) {
        //If key is availble in current choice keep the file else trash it
        if (in_array($key, $attachments_to_keep)) {
          $attachment_url = wp_get_attachment_url($key);
          $pp_image_array[$key] = $attachment_url;
        } else {
          wp_delete_attachment($key, true);
        }
      }

    }

    if ($_FILES) {
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
          $_FILES = array("files" => $file);
          foreach ($_FILES as $file => $array) {
            $newupload = $this->insert_attachment($file, 0);
            $attachment_url = wp_get_attachment_url($newupload);
            $pp_image_array[$newupload] = $attachment_url;
          }
        }
      }
    }

    //Image array
    if (isset($_POST['company_name']) && $_POST['company_name'] != '') {

      $service_type = $_POST['service_type'];
      if ($service_type == 'seller') {
        $minority_category = $_POST['minority_category'];
      } else
        $minority_category = '';


      $indstry = array();
      if (isset($_POST['industry'])) {
        $indstry = $_POST['industry'];
        if (in_array('other', $indstry)) {
          $oth_indus = explode(",", $_POST['other_industry']);
        } else
          $oth_indus = "";
      }

      $certification = array();
      if (isset($_POST['certifications'])) {
        $certification = $_POST['certifications'];
        if (in_array('other', $certification)) {
          $oth_cert = explode(",", $_POST['other_certification']);
        } else
          $oth_cert = "";
      }

      $services = array();
      if (isset($_POST['services'])) {
        $services = $_POST['services'];
        if (in_array('other', $services)) {
          $oth_serv = explode(",", $_POST['other_services']);
        } else
          $oth_serv = "";
      }

      /**
       * 'mm365_company_phone' added since v2.9. Meta to identify 
       * 
       */

      $post_information = array(
        'post_title' => wp_strip_all_tags($_POST['company_name']),
        'meta_input' => array(
          'mm365_company_country' => sanitize_text_field($_POST['company_country']),
          'mm365_company_address' => sanitize_text_field($_POST['company_address']),
          'mm365_company_state' => sanitize_text_field($_POST['company_state']),
          'mm365_company_city' => sanitize_text_field($_POST['company_city']),
          'mm365_zip_code' => sanitize_text_field($_POST['zip_code']),
          'mm365_contact_person' => sanitize_text_field($_POST['contact_person']),
          'mm365_company_phone_type' => sanitize_text_field($_POST['primary_phone_type']),
          'mm365_company_phone' => sanitize_text_field($_POST['phone']),
          'mm365_company_email' => sanitize_email($_POST['company_email']),
          'mm365_alt_contact_person' => sanitize_text_field($_POST['alt_contact_person']),
          'mm365_alt_phone' => sanitize_text_field($_POST['alt_phone']),
          'mm365_alt_email' => sanitize_email($_POST['alt_email']),
          'mm365_website' => sanitize_text_field($_POST['website']),
          'mm365_service_type' => $service_type,
          'mm365_minority_category' => $minority_category,
          'mm365_number_of_employees' => sanitize_text_field($_POST['number_of_employees']),
          'mm365_size_of_company' => sanitize_text_field($_POST['size_of_company']),
          'mm365_main_customers' => json_encode(array_filter((array) $_POST['main_customers'], array($this, "purge_empty"))),
          'mm365_company_description' => wp_kses_post($_POST['company_description']),
          'mm365_company_docs' => $pp_image_array,
          'mm365_company_name' => wp_strip_all_tags($_POST['company_name'])
        ),
        'post_type' => 'mm365_companies',
        'post_status' => $status_mode,
        'ID' => $_POST['company_id']
      );

      //Loop and save NAICS
      $comp_id = $_POST['company_id'];
      $new_naics = array_filter((array) $_POST['naics_codes'], array($this, "purge_empty"));
      delete_post_meta($comp_id, 'mm365_naics_codes');
      foreach ($new_naics as $data) {
        add_post_meta($comp_id, 'mm365_naics_codes', $data);
      }

      //certification
      $new_certification = array_filter((array) $certification, array($this, "purge_empty"));
      delete_post_meta($comp_id, 'mm365_certifications');
      foreach ($new_certification as $data) {
        add_post_meta($comp_id, 'mm365_certifications', $data);
      }
      if (!empty($oth_cert)) {
        foreach ($oth_cert as $data) {
          if ($data != 'other')
            add_post_meta($comp_id, 'mm365_certifications', $data);
        }
      }

      //Services
      $new_services = array_filter((array) $services, array($this, "purge_empty"));
      delete_post_meta($comp_id, 'mm365_services');
      foreach ($new_services as $data) {
        add_post_meta($comp_id, 'mm365_services', $data);
      }
      if (!empty($oth_serv)) {
        foreach ($oth_serv as $data) {
          if ($data != 'other')
            add_post_meta($comp_id, 'mm365_services', $data);
        }
      }

      //Industry
      $new_industry = array_filter((array) $indstry, array($this, "purge_empty"));
      delete_post_meta($comp_id, 'mm365_industry');
      foreach ($new_industry as $data) {
        add_post_meta($comp_id, 'mm365_industry', $data);
      }
      if (!empty($oth_indus)) {
        foreach ($oth_indus as $data) {
          if ($data != 'other')
            add_post_meta($comp_id, 'mm365_industry', $data);
        }
      }

      //Verify if company name is changed then update new name to MR
      $new_name = wp_strip_all_tags($_POST['company_name']);
      if (get_the_title($comp_id) != $new_name) {
        $this->matchrequest_companyname_revision($comp_id, $new_name);
      }

      $post_id = wp_update_post($post_information);

      //Delete capability statements if switched service type to buyer
      if ($service_type == 'buyer') {
        $active_attachments = get_post_meta($comp_id, 'mm365_company_docs', true);
        foreach ($active_attachments as $key => $value) {
          wp_delete_attachment($key, true);
        }
        update_post_meta($comp_id, 'mm365_company_docs', "");
      }
      if ($service_type != 'buyer') {
        //Update international assistance from MMSDC
        $int_assi = array_filter((array) $_POST['international_assistance'], array($this, "purge_empty"));
        delete_post_meta($post_id, 'mm365_international_assistance');
        foreach ($int_assi as $data) {
          add_post_meta($post_id, 'mm365_international_assistance', $data);
        }
      } else {
        delete_post_meta($post_id, 'mm365_international_assistance');
      }


      //Serviceable countries and states
      if ($service_type != 'buyer') {
        $servicebale_countries = $_POST['serviceable_countries'];
        delete_post_meta($post_id, 'mm365_cmp_serviceable_countries');
        foreach ($servicebale_countries as $value) {
          add_post_meta($post_id, 'mm365_cmp_serviceable_countries', $value);
        }


        $servicebale_states = $_POST['serviceable_states'];
        delete_post_meta($post_id, 'mm365_cmp_serviceable_states');
        foreach ($servicebale_states as $value) {
          add_post_meta($post_id, 'mm365_cmp_serviceable_states', $value);
        }
      } else {
        delete_post_meta($post_id, 'mm365_cmp_serviceable_countries');
        delete_post_meta($post_id, 'mm365_cmp_serviceable_states');
      }
      //Map council
      $council_id = sanitize_text_field($_POST['company_council']);
      update_post_meta($post_id, 'mm365_company_council', $council_id);

      //Update USER META 
      $this->update_user_council($this->user->ID, $council_id);


      return $this->show($post_id, $status_mode);

    } else
      echo "Error";



    wp_die();



  }

  /**
   * Show Company 
   * add_filter required mm365_companies_show
   * @param int $cmp_id
   * @param string $default_stat
   * @param bool $ajax - whether its an ajax return or not default TRUE
   */
  function show(int $cmp_id, string $default_stat = 'draft', bool $ajax = true)
  {


    $args = array(
      'p' => $cmp_id,
      'post_type' => 'mm365_companies',
      'post_status' => $default_stat,
      'posts_per_page' => 1,
      'orderby' => 'title',
    );


    $loop = new \WP_Query($args);
    while ($loop->have_posts()):

      $loop->the_post();

      $current_council = get_post_meta(get_the_ID(), 'mm365_company_council', true);
      $service_type = get_post_meta(get_the_ID(), 'mm365_service_type', true);

      //Shows a notice if the company hasn't published yet
      if ($default_stat == 'draft') {
        echo '<div class="alert alert-danger" role="alert">
              Attention! Please preview the company details you have entered and click on submit button to complete the registration process.
                </div>';
      }

      echo '<section  class="company_preview" data-intro="Details of the company" data-step="1">
        
            <div class="row mbo-30">
                 <div class="col-lg-5">
                  <h6>Company name</h6>
                  <h3 class="heading-medium">' . get_the_title() . '</h3></div>
            
            <div class="col-lg-2">
              <h6>Service type</h6>
              <p>' . apply_filters('mm365_company_get_type', $service_type) . '</p>
            </div>
        
           <div class="col-lg-3">
               <h6>Council</h6>
               <p>' . apply_filters('mm365_council_get_info', $current_council, 'shortname') . " - " . apply_filters('mm365_council_get_info', $current_council, 'name') . '</p></div>';

      //-------------- ATTENTION --------------
      if (apply_filters('mm365_certification_is_certified', get_the_ID()) == TRUE):
        echo '<div class="col-lg-2 text-right">
               <img src="' . get_template_directory_uri() . '/assets/images/mmsdc_certified.png" alt="Certified Supplier"/>
               </div>';
      endif;

      echo '</div>';

      $seller_title = 'Description of services or products offered';
      $buyer_title = 'Company Information';

      /* Company Description */
      echo '<div class="row mbo-10">
                  <div class="col-lg-12">
                    <h6>';
      echo ($service_type == 'buyer') ? $buyer_title : $seller_title;
      echo ' </h6>
                  </div>
            </div>
        
            <div class="row mbo-30">
             <div class="col-lg-12 break-words">' .
        get_post_meta(get_the_ID(), 'mm365_company_description', true);

      if (in_array('mmsdc_manager', (array) $this->user->roles)) {
        echo '<br/><br/><a href="' . add_query_arg('_wpnonce', wp_create_nonce('edit_company_description'), site_url('edit-company-description?cmp_id=' . get_the_ID())) . '" class="text-danger"><i class="fas fa-arrow-up"></i> Edit Details</a>';
      }

      echo '</div><div class="col-12 d-block d-sm-none pbo-30"></div>
            </div>
            
            <div class="row mbo-30">
';

                    //Shows all NAICS code
                    echo '<div class="col-lg-3">
                          <h6> NAICS codes</h6>';
                          foreach (get_post_meta(get_the_ID(), 'mm365_naics_codes', false) as $key => $value) {
                          $naics_2[] = $value;
                          }
                          if (isset($naics_2)) {
                          echo implode(', ', $naics_2);
                          } else
                          echo "-";

                      echo '</div>

                      <div class="col-lg-3">
                      <h6>Contact person</h6>
                      <p>' . $this->get_companymeta(get_the_ID(), 'mm365_contact_person') . '</p>
                      </div>
                      <div class="col-lg-4">
                      <h6>Company Address</h6>
                      <p>' . get_post_meta(get_the_ID(), 'mm365_company_address', true) . '</p>
                      </div>
            
            </div>';

      echo '<div class="row mbo-30">
              <div class="col-lg-3">
             <h6>Country</h6>
              <p>' .
               
               $this->get_countryname($this->get_companymeta($cmp_id, 'mm365_company_country'))
              . '</p>
              </div>
              <div class="col-lg-3">
              <h6>State</h6>
              <p>' . $this->get_statename($this->get_companymeta($cmp_id, 'mm365_company_state')) . '</p>
              </div>
              <div class="col-lg-3">
              <h6>City</h6>
              <p>' . $this->get_cityname($this->get_companymeta($cmp_id, 'mm365_company_city')) . '</p>
              </div>
              <div class="col-lg-3">
              <h6>ZIP code</h6>
              <p>' . $this->get_companymeta(get_the_ID(), 'mm365_zip_code') . '</p>
              </div>
            </div>';

      $phone_type = $this->get_companymeta(get_the_ID(), 'mm365_company_phone_type', TRUE, NULL);


      echo '<div class="row mbo-30">
              <div class="col-lg-3">
                <h6>Phone</h6>
                <p>' . $this->get_companymeta(get_the_ID(), 'mm365_company_phone');

      //PHP 8+ required
      if ($phone_type != NULL) {
        $phn_type_label = match ($phone_type) {
          'mobile' => esc_html('Mobile'),
          'landphone' => esc_html('Land Phone'),
        };
        echo " (" . $phn_type_label . ")";
      }


      echo '</p>
              </div>
              <div class="col-lg-3">
                <h6>Email</h6>
                <p>' . $this->get_companymeta(get_the_ID(), 'mm365_company_email') . '</p>
              </div>   

              <div class="col-lg-3">
                <h6>Company website</h6>
                <p style="word-break:break-all">';

      $web_url = $this->get_companymeta(get_the_ID(), 'mm365_website');

      if ($web_url != '-') {
        if (filter_var($web_url, FILTER_VALIDATE_URL)) {
          echo "<a href='" . $web_url . "' target='_blank'>" . $web_url . "</a>";
        } else {
          echo "<a href='http://" . $web_url . "' target='_blank'>" . $web_url . "</a>";
        }
      } else
        echo $web_url;

      echo '</p>
                  </div>     
              </div>';


      echo '<div class="row mbo-30">
                    <div class="col-lg-3">
                      <h6>Alternate contact person</h6>
                      <p>' . $this->get_companymeta(get_the_ID(), 'mm365_alt_contact_person') . '</p>
                  </div>
                 <div class="col-lg-3">
                   <h6>Alternate phone</h6>
                    <p>' . $this->get_companymeta(get_the_ID(), 'mm365_alt_phone') . '</p>
                 </div> 
                 <div class="col-lg-3">
                   <h6>Alternate email</h6>
                   <p>' . $this->get_companymeta(get_the_ID(), 'mm365_alt_email') . '</p>
                 </div>      
                </div>';



      echo '<div class="row mbo-30">
              <div class="col-lg-3">
                <h6>Industries</h6>';
      foreach ((get_post_meta(get_the_ID(), 'mm365_industry')) as $key => $value) {
        $industries[] = $value;
      }
      if (isset($industries)){
        echo implode(', ', $industries);
      }else{
        echo "-";
      }
      echo '</div><div class="col-12 d-block d-sm-none pbo-30"></div>   
        
              <div class="col-lg-3">
                <h6>Company services</h6><p>';

      foreach ((get_post_meta(get_the_ID(), 'mm365_services')) as $key => $value) {
        $services[] = $value;
      }

      if (isset($services)):
        echo implode(', ', $services);
      else:
        echo "-";
      endif;
      echo '</p></div>';


      if ($service_type == 'seller') {
        echo '<div class="col-lg-3">
                <h6>Locations where the services or products are available</h6>';

        $this->company_serviceable_locations(get_the_ID());
        echo '</div>';

      }
      echo '</div>';


      /* General Information */
      echo '<div class="row mbo-30">';

      if ($service_type == 'seller') {

        echo '<div class="col-lg-3">
              <h6>Minority classification</h6>
              <p>' . $this->expand_minoritycode(get_post_meta(get_the_ID(), 'mm365_minority_category', true)) . '</p>
              </div>';

        echo '<div class="col-lg-3">
               <h6>Capability statement</h6>';
        $company_docs = get_post_meta(get_the_ID(), 'mm365_company_docs', true);
        if (!empty($company_docs)) {
          foreach ($company_docs as $attachment_id => $attachment_url) {
            $filetype = wp_check_filetype(get_attached_file($attachment_id));
            echo '<div class="filecard">
                        <a data-fancybox href="' . $attachment_url . '" target="_blank">
                        <img height="36px" data-file="' . $attachment_id . '" src="' . get_template_directory_uri() . '/assets/images/' . $filetype['ext'] . '.svg" />
                        <span>' . basename(get_attached_file($attachment_id)) . "</span></a>
                        </div>";
          }
          echo "<small>(Please click on the file name to open/download the file)</small><br/>";
        } else
          echo "-";

        echo '</div>';

      }

      echo '<div class="col-lg-3"><div class="col-12 d-block d-sm-none pbo-30"></div>
                <h6> Current customers </h6><p>';

      $current_customers = get_post_meta(get_the_ID(), 'mm365_main_customers', true);
      if ($current_customers != '') {
        foreach (json_decode($current_customers) as $key => $value) {
          $main_customers[] = $value;
        }
      } else {
        $main_customers = array();
      }

      echo (!empty($main_customers)) ? implode(', ', $main_customers) : '-';

      echo '</p></div>
              </div>';



      /* Company Details */
      echo '<div class="row mbo-10"><div class="col-lg-12"></div></div>
                   <div class="row mbo-30">';

      if ($service_type == 'seller') {

        echo '<div class="col-lg-3">
              <h6>International Assistance from Council</h6>';

        $int_assi = get_post_meta(get_the_ID(), 'mm365_international_assistance');

        echo "<p>";
        echo (!empty($int_assi)) ? implode(', ', $int_assi) : '-';
        echo '</p>  
              </div>';

      }

      echo '<div class="col-lg-3">
              <h6>Size of company</h6>' .
        $this->get_companymeta(get_the_ID(), 'mm365_size_of_company')
        . '</div>
        
              <div class="col-lg-3"><div class="col-12 d-block d-sm-none pbo-30"></div>
              <h6>Number of employees</h6>' .
        $this->get_companymeta(get_the_ID(), 'mm365_number_of_employees')
        . '</div>
        
              <div class="col-lg-3"><div class="col-12 d-block d-sm-none pbo-30"></div>
              <h6>Industry Certifications</h6>';


      //shows all certifications 
      foreach ((get_post_meta(get_the_ID(), 'mm365_certifications')) as $key => $value) {
        $certifications[] = $value;
      }

      echo (!empty($certifications)) ? implode(', ', $certifications) : '-';


      echo '</div>
            </div>
            </section>';

      //Controls visible only if the post belongs to current user

      if ($this->user->ID == get_the_author_meta('ID')):
        if ($default_stat == 'draft') {
          echo '<div class="row  mto-30">
                  <div class="col-lg-12 text-right">
                    <a href="' . site_url() . '/edit-company/?cid=' . get_the_ID() . '" class="btn btn-primary">Edit</a> 
                    <a href="' . site_url() . '/submit-company/?cid=' . get_the_ID() . '" class="btn btn-primary">Submit</a>
                  <div>
                  <div>';
        } else {
          echo '<div class="row mto-30">
                <div class="col-lg-12 text-right" >
                <a data-intro="Edit the details of your company" data-step="2" href="' . site_url() . '/edit-active-company?cid=' . get_the_ID() . '" class="btn btn-primary">Edit</a> </div>
                <div>';
        }
      endif;

      echo "</div></div>";

    endwhile;
    wp_reset_postdata();

    //AJAX return should end with die() else it will return 0
    if ($ajax == TRUE) {
      die();
    }


  }


  /**
   * Delete a company - from draft state only
   * @param int $cid
   */
  function delete($cid)
  {
    if (wp_delete_post($cid, true)) {
      return true;
    } else
      return false;
  }


  /**
   * @param int $company_id
   * @param string $new_name
   * This is used to revise company name in match requests posts if there is change
   */
  function matchrequest_companyname_revision($company_id, $new_name)
  {

    //Find all match requests with company id (mm365_requester_company_id)
    $all_matchrequests = array(
      'posts_per_page' => -1,
      // No limit
      'fields' => 'ids',
      // Reduce memory footprint
      'post_type' => 'mm365_matchrequests',
      'meta_query' => array(
        array(
          'key' => 'mm365_requester_company_id',
          'value' => $company_id,
          'compare' => '=',
        ),
      )
    );

    $all_matchrequest_query = new \WP_Query($all_matchrequests);
    while ($all_matchrequest_query->have_posts()):
      $all_matchrequest_query->the_post();
      $mr_id = get_the_ID();
      update_post_meta($mr_id, "mm365_requester_company_name", $new_name);
    endwhile;
    wp_reset_postdata();


  }


  /**
   * v2.1 Onwards
   * Super Admin editing company details
   * This function is only for super admin role(mmsdc_admin)
   */
  function update_company_description()
  {

    //Get contents
    $content = $_POST['company_description'];
    $company_id = $_POST['company_id'];

    if (update_post_meta($company_id, 'mm365_company_description', $content)) {
      echo 'success';
    } else {
      echo 'fail';
    }

    wp_die();
  }

  /**
   * @param int $cmp_id
   * @param string $meta_key
   * @param bool $single - TRUE to return array FALSE to return single
   */
  function get_companymeta(int $cmp_id, string $meta_key, bool $single = true, $fallback = '-')
  {

    $meta = get_post_meta($cmp_id, $meta_key, $single);

    return (!empty($meta)) ? $meta : $fallback;

  }



  /**
   * Shows staes from multiple countries grouped
   * Used for AJAX
   */
  public function serviceable_states()
  {
    //Check nonce
    $countries = $_REQUEST['countries'];
    $states = $_REQUEST['states'];

    $this->get_serviceable_states($countries, $states);

    wp_die();
  }

  /**
   * @param array $countries 
   * @param array $states 
   * This is for using it as filter - (edit company page)
   * 
   */
  public function preload_serviceable_states($countries, $states)
  {

    $this->get_serviceable_states($countries, $states);

  }




  /**
   * Get serviceable states
   * @param array $countries 
   * @param array $states - may contain 'all states' option from specific country 
   */
  public function get_serviceable_states($countries, $states)
  {

    /*
     * Check wether user has opted for 'all states' in any country 
     * then remove all the 'states ids' corresponding to that country
     */

    $filter_all_states = $states;
    $all_state_selected_countries = array();

    if (!empty($filter_all_states)) {

      //Filtering the the array
      foreach ($filter_all_states as $key => $val) {
        if (is_numeric($val))
          unset($filter_all_states[$key]);
      }

      //Prepare 'all state' selected country ids - (ie Services provided to all state in the selected Countries)
      foreach ($filter_all_states as $value) {
        $id = explode("_", $value);
        array_push($all_state_selected_countries, $id[1]);
      }

    }


    //Get states
    foreach ((array) $countries as $country_id) {

      echo '<optgroup label="' . $this->get_countryname($country_id) . '">';

      if (in_array($country_id, (array) $all_state_selected_countries)) {
        $only_all = TRUE;
      } else
        $only_all = FALSE;

        print_r($filter_all_states);

      //$this->states_dropdown($country_id,(array) $states,TRUE, $only_all);
      $this->states_dropdown( $country_id, (array)$states, TRUE, $only_all);

      echo '</optgroup>';
    }


  }



  /**
   * @param int $company_id
   * @param int $mode -- return or echo the result
   */
  function company_serviceable_locations($company_id, $mode = '1')
  {
    $countries = get_post_meta($company_id, 'mm365_cmp_serviceable_countries');
    $states = get_post_meta($company_id, 'mm365_cmp_serviceable_states');
    //return $states_array;
    if ($mode == '1') {
      echo $this->multi_countries_state_display($countries, $states);
    } else
      return $this->multi_countries_state_display($countries, $states);
  }







}
<?php
namespace Mm365;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class Certification
{

  use CompaniesAddon;
  use Mm365Files;
  use NotificationAddon;
  use CouncilAddons;
  use CertificateAddon;

  function __construct()
  {


    add_action('wp_enqueue_scripts', array($this, 'assets'), 11);

    //Create meeting
    add_action('wp_ajax_save_certificate', array($this, 'save_certificate'));

    //Listing 
    add_action('wp_ajax_certificates_submitted', array($this, 'certificates_submitted'));

    //Delete 
    add_action('wp_ajax_delete_certificate', array($this, 'delete_certificate'));

    //Admin Listing
    add_action('wp_ajax_admin_certificates_listing', array($this, 'admin_certificates_listing'));

    //Admin verification
    add_action('wp_ajax_admin_verification', array($this, 'admin_verification'));


    add_filter('mm365_certification_is_certified', array($this, 'is_certified'), 10, 1);
    add_filter('mm365_admin_quickreports_certification', array($this, 'quick_report_download'), 11, 3);
    add_filter('mm365_admin_filteredreports_certification', array($this, 'generate_report'), 11, 0);

    add_filter('mm365_certification_can_council_access', array($this,'can_council_access'),10,3);

  }


  function assets()
  {
    if (wp_register_script('mm365_certificates', plugins_url('matchmaker-core/assets/mm365_certificates.js'), array('jquery'), false, TRUE)) {
      wp_enqueue_script('mm365_certificates');
      wp_localize_script('mm365_certificates', 'certificationAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce("certification_ajax_nonce")
      ));

    }
  }

  /*------------------------------------------------------------
     Save certificates
     --------------------------------------------------------------*/
  function save_certificate()
  {

    //If date of expiry is lesser than 7 days from today do not accept
    $today = date('Y-m-d');
    $seventh_day = (strtotime($today) + 10080 * 60);
    $expiry_stamp = strtotime(sanitize_text_field($_POST['expiry_date']));

    if ($expiry_stamp <= $seventh_day) {
      echo '3';
      die();
    }

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

      $submitting_company = wp_strip_all_tags(get_the_title(sanitize_text_field($_POST['uploaded_company_id'])));
      $user = wp_get_current_user();
      $post_information = array(
        'post_title' => $submitting_company,
        'meta_input' => array(
          'mm365_submitted_by' => sanitize_text_field($_POST['uploaded_company_id']),
          'mm365_submitted_council' => sanitize_text_field($_POST['company_council_id']),
          'mm365_expiry_date' => sanitize_text_field($_POST['expiry_date']),
          'mm365_expiry_date_timestamp' => strtotime(sanitize_text_field($_POST['expiry_date'])),
          'mm365_certificate' => $pp_image_array,
          'mm365_certificate_status' => 'pending',
          'mm365_submitted_companyname' => $submitting_company,
        ),
        'post_type' => 'mm365_certification',
        'post_status' => 'publish',
        'post_author' => $user->ID
      );

      $post_id = wp_insert_post($post_information);

      echo '1';
      die();

    } else
      echo '0';
    die();

  }


  /*------------------------------------------------------------
      Delete certificate
      --------------------------------------------------------------*/

  function delete_certificate()
  {

    //Get post ID
    $nonce = $_POST['nonce'];
    $cert_id = $_POST['cert_id'];

    $user = wp_get_current_user();
    $auth = get_post($cert_id); // gets author from post
    $authid = $auth->post_author; // gets author id for the post

    if (!wp_verify_nonce($nonce, 'certification_ajax_nonce') or !is_user_logged_in() or ($user->ID != $authid)) {
      echo '0';
      die();

    } else {

      //Get attchment IDS and delete them
      $certificate = get_post_meta($cert_id, 'mm365_certificate', true);
      foreach ($certificate as $key => $value) {
        wp_delete_attachment($key, true);
      }
      wp_delete_post($cert_id, true);
      //delete the post
      echo '1';
      die();
    }


  }



  /*------------------------------------------------------------
     Certificate submitted - Listing for user
     --------------------------------------------------------------*/
  function certificates_submitted()
  {

    $cmp_id = $this->get_user_company_id(wp_get_current_user());
    header("Content-Type: application/json");

    $request = $_GET;
    $columns = array(
      0 => 'certificate',
      1 => 'date_uploaded',
      2 => 'date_of_expiry',
      3 => 'notes',
      4 => 'status',
      5 => 'view',
      6 => 'edit',
    );

    $args = array(
      'post_type' => 'mm365_certification',
      'post_status' => 'publish',
      'posts_per_page' => $request['length'],
      'offset' => $request['start'],
      'order' => 'DESC',
      'order_by' => 'modified',
      'meta_query' => array(
        array(
          'key' => 'mm365_submitted_by',
          'value' => esc_html($_COOKIE['active_company_id']),
          'compare' => '=',
        ),
      )
    );

    if (isset($request['order'])):
      if ($request['order'][0]['column'] == 2) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
        $args['meta_key'] = 'mm365_expiry_date';
      } elseif ($request['order'][0]['column'] == 4) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
        $args['meta_key'] = 'mm365_certificate_status';
      }
    endif;

    if (!empty($request['search']['value'])) { // When datatables search is used


      $args['orderby'] = array('modified' => 'DESC');

      $args['meta_query'] = array(
        array(
          'key' => 'mm365_submitted_by',
          'value' => $cmp_id,
          'compare' => '=',
        ),
        array(
          'relation' => 'OR',
          array(
            'key' => 'mm365_certificate',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_expiry_date',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_submitted_by',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_admin_note',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_certificate_status',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          )
        )

      );
    }

    $certification = new \WP_Query($args);
    $totalData = $certification->found_posts;
    //echo $totalData; print_r($args); die();

    if ($certification->have_posts()) {
      while ($certification->have_posts()) {

        $certification->the_post();

        $certificate = get_post_meta(get_the_ID(), 'mm365_certificate', true);
        $expiry = get_post_meta(get_the_ID(), 'mm365_expiry_date', true);
        $status = get_post_meta(get_the_ID(), 'mm365_certificate_status', true);
        $note = get_post_meta(get_the_ID(), 'mm365_admin_note', true);


        foreach ($certificate as $key => $value) {
          $cert = '<a data-fancybox href="' . $value . '"">' . basename(get_attached_file($key)) . '</a>';
        }

        $nestedData = array();
        $nestedData[] = $cert;
        $nestedData[] = get_post_time("m/d/Y");
        $nestedData[] = $expiry;
        $nestedData[] = ($note) ? substr($note, 0, 50) : '-';
        $nestedData[] = "<span class='meeting_status " . $status . "'>" . preg_replace('/\_+/', ' ', $status) . "</span>";
        $nestedData[] = '<a href="' . add_query_arg('_wpnonce', wp_create_nonce('certificate_details'), site_url() . '/certificate-details?cert=' . get_the_ID()) . '">View</a>';
        if ($status == 'pending') {
          $nestedData[] = '<a class="delete-certificate" data-certificate="' . get_the_ID() . '"  data-redirect="' . esc_url(site_url() . "/certificate-upload") . '" href="#">Delete</a>'; //Edit mode pending
        } else {
          $nestedData[] = '<span class="text-disabled">Delete</span>';
        }

        $data[] = $nestedData;

      }

      wp_reset_query();
      $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalData),
        "data" => $data
      );
      echo json_encode($json_data);

    } else {
      $json_data = array(
        "data" => array()
      );
      echo json_encode($json_data);
    }
    wp_die();


  }




  /*------------------------------------------------------------
      Certificate submitted - Listing for admin
      --------------------------------------------------------------*/
  function admin_certificates_listing()
  {

    function mm365_company_title_filter_certificate($where, &$wp_query)
    {
      global $wpdb;
      if ($search_term = $wp_query->get('search_company_title')):
        $search_term = $wpdb->esc_like($search_term);
        $search_term = ' \'%' . $search_term . '%\'';
        $title_filter_relation = (strtoupper($wp_query->get('search_company_title_relation')) == 'OR' ? 'OR' : 'AND');
        $where .= ' ' . $title_filter_relation . ' ' . $wpdb->posts . '.post_title LIKE ' . $search_term . ' AND ' . $wpdb->posts . '.post_type = "mm365_certification"';
      endif;
      return $where;
    }

    $user = wp_get_current_user();

    $council_id = apply_filters('mm365_helper_get_usercouncil', $user->ID);

    //If current user is not council manager, check if super admin is filterin view with specific council
    $is_admin_filtering = 'no';
    if ($_REQUEST['sa_council_filter'] != '' and $council_id == '') {
      $is_admin_filtering = 'yes';
    }

    //override council id by admin selected council id while filtering
    if ($council_id == '') {
      $council_id = $_REQUEST['sa_council_filter'];
    }

    //If admin is not filtering or selected all council, this will be skipped in council_id check below


    //Show all info council manager
    if (in_array('council_manager', (array) $user->roles) or $is_admin_filtering == 'yes') {
      $add_council_restriction = array(
        'key' => 'mm365_submitted_council',
        'value' => $council_id,
        'compare' => '='
      );
    } else
      $add_council_restriction = NULL;



    header("Content-Type: application/json");
    $request = $_POST;
    $columns = array(
      0 => 'certificate',
      1 => 'council',
      2 => 'date_uploaded',
      3 => 'date_of_expiry',
      4 => 'notes',
      5 => 'status',
      6 => 'view',
      7 => 'edit',
    );

    if (isset($request['status']) and $request['status'] != '') {
      $status = array($request['status']);
      ($status == 'pending') ? $col = 'post_date' : $col = 'post_modified';
      $priod = array('column' => $col, 'after' => '1 ' . $request['period'] . ' ago');
    } else {
      $status = array("pending", "expired", "verified", "unapproved");
      $priod = NULL;
    }

    $args = array(
      'post_type' => 'mm365_certification',
      'post_status' => 'publish',
      'posts_per_page' => $request['length'],
      'offset' => $request['start'],
      'order' => 'DESC',
      'order_by' => 'post_date',
      'date_query' => array($priod),
      'meta_query' => array(
        array(
          'key' => 'mm365_certificate_status',
          'value' => $status,
          'compare' => 'IN'
        ),
        $add_council_restriction
      )
    );

    if (isset($request['order'])):
      if ($request['order'][0]['column'] == 2) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
        $args['meta_key'] = 'mm365_expiry_date';
      } elseif ($request['order'][0]['column'] == 4) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
        $args['meta_key'] = 'mm365_certificate_status';
      }
    endif;

    //Council ID condition to search
    if ($council_id == '') {
      $conditional_council_search = array(
        'key' => 'mm365_submitted_council',
        'value' => sanitize_text_field($request['search']['value']),
        'compare' => 'LIKE'
      );
    } else {
      $conditional_council_search = NULL;
    }


    if (!empty($request['search']['value'])) { // When datatables search is used

      $args['search_company_title'] = $request['search']['value'];
      $args['search_company_title_relation'] = 'OR';
      $args['orderby'] = array('post_date' => 'DESC');
      $args['meta_query'] = array(
        array(
          array(
            'key' => 'mm365_certificate_status',
            'value' => $status,
            'compare' => 'IN'
          ),
          $add_council_restriction
        ),
        array(
          'relation' => 'OR',
          array(
            'key' => 'mm365_certificate',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_expiry_date',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_submitted_by',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_admin_note',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_certificate_status',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_submitted_companyname',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          $conditional_council_search
        )

      );
    }

    $certification = new \WP_Query($args);


    $totalData = $certification->found_posts;
    //echo $totalData; print_r($args); die();

    if ($certification->have_posts()) {
      while ($certification->have_posts()) {

        $certification->the_post();

        $certificate = get_post_meta(get_the_ID(), 'mm365_certificate', true);
        $expiry = get_post_meta(get_the_ID(), 'mm365_expiry_date', true);
        $status = get_post_meta(get_the_ID(), 'mm365_certificate_status', true);
        $submitted = get_post_meta(get_the_ID(), 'mm365_submitted_by', true);
        $note = get_post_meta(get_the_ID(), 'mm365_admin_note', true);
        $sumbitter_council = get_post_meta(get_the_ID(), 'mm365_submitted_council', true);

        $nestedData = array();
        $nestedData[] = $this->certified_badge($submitted, true) . "<a href='" . site_url() . "/view-company?cid=" . $submitted . "'>" . get_the_title($submitted) . "</a>";
        if ($council_id == '' or $is_admin_filtering == 'yes') {
          $nestedData[] = apply_filters('mm365_council_get_info', $sumbitter_council);
        }
        $nestedData[] = get_post_time("m/d/Y");
        $nestedData[] = $expiry;
        $nestedData[] = ($note != NULL) ? substr($note, 0, 50) : '-';
        $nestedData[] = "<span class='meeting_status " . $status . "'>" . preg_replace('/\_+/', ' ', $status) . "</span>";
        //if($council_id !=''):
        $nestedData[] = '<a href="' . add_query_arg('_wpnonce', wp_create_nonce('admin_certificate_details'), site_url() . '/admin-certificate-details?cert=' . get_the_ID()) . '">View</a>';
        // else:
        //   $nestedData[] = '';
        // endif;
        $data[] = $nestedData;

      }

      wp_reset_query();
      $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalData),
        "data" => $data
      );
      echo json_encode($json_data);

    } else {
      $json_data = array(
        "data" => array()
      );
      echo json_encode($json_data);
    }
    wp_die();


  }





  /*------------------------------------------------------------
     Admin verification action
      --------------------------------------------------------------*/

  function admin_verification()
  {

    //Get post ID
    $nonce = $_POST['nonce'];
    $cert_id = $_POST['cert_id'];
    $status = $_POST['status'];
    $note = $_POST['note'];

    $user = wp_get_current_user();


    if (!wp_verify_nonce($nonce, 'certification_ajax_nonce') or !is_user_logged_in()) {
      echo '0';
      die();

    } else {

      $company_id = get_post_meta($cert_id, 'mm365_submitted_by', true);
      $mail_id = get_post_meta($company_id, 'mm365_company_email', true);
      $contact_name = get_post_meta($company_id, 'mm365_contact_person', true);
      $council_id = get_post_meta($cert_id, 'mm365_submitted_council', true);

      switch ($status) {
        case 'verified':
          //status and act 'approved'
          update_post_meta($cert_id, 'mm365_certificate_status', 'verified');
          update_post_meta($cert_id, 'mm365_admin_note', $note);
          update_post_meta($cert_id, 'mm365_verifiedby', $user->ID);

          //Add to company info
          if (metadata_exists('post', $company_id, 'mm365_certification_status')) {
            add_post_meta($company_id, 'mm365_certification_status', 'verified');
          } else {
            delete_post_meta($company_id, 'mm365_certification_status', 'verified');
            add_post_meta($company_id, 'mm365_certification_status', 'verified');
          }


          //Notify approval
          $this->admin_action_notices($mail_id, $contact_name, $note, 'verified', $council_id);

          echo '1';
          break;

        case 'rejected':
          update_post_meta($cert_id, 'mm365_certificate_status', 'unapproved');
          update_post_meta($cert_id, 'mm365_admin_note', $note);
          update_post_meta($cert_id, 'mm365_verifiedby', $user->ID);

          if (metadata_exists('post', $company_id, 'mm365_certification_status')) {
            delete_post_meta($company_id, 'mm365_certification_status', 'verified');
          }

          //Notify rejection
          $this->admin_action_notices($mail_id, $contact_name, $note, 'unapproved', $council_id);

          echo '2';
          break;
      }

      die();
    }


  }


  /*------------------------------------------------------------
   Check if company is certified
   --------------------------------------------------------------*/
  function is_certified($company_id)
  {

    //look for company id mm365_submitted_by and status as approved only one
    //mm365_certificate_status

    //Check if its a buyer company 
    $status = get_post_meta($company_id, 'mm365_certification_status', true);

    if (!empty($status) and $status == 'verified') {
      return TRUE;
    } else
      return FALSE;


  }




  /*------------------------------------------------------------
      Notify users about expiry
  --------------------------------------------------------------*/


  function notification_mail($mail_id, $contact_name, $period, $council_id = NULL)
  {
    //$mail_id
    //$contact_name
    //$period - Change content based on period
    $link = site_url() . '/certificate-verification/';

    //Get council short name
    if ($council_id != ''):
      $council_shortname = " " . get_post_meta($council_id, 'mm365_council_shortname', true) . " ";
    else:
      $council_shortname = ' ';
    endif;


    if ($period != '0') {
      $subject = 'Your' . $council_shortname . 'MBE certificate is expiring in ' . $period . ' days';
      $title = $subject;
      $content = '
            <p>Hi ' . $contact_name . ',</p>
            <p>Your' . $council_shortname . 'MBE certificate is expiring in ' . $period . ' days. Please upload your updated certificate at the earliest by clicking on the below button.</p>';
    } else {
      $subject = 'Your' . $council_shortname . 'MBE certification is expiring tommorow';
      $title = $subject;
      $content = '
    <p>Hi ' . $contact_name . ',</p>
    <p>Your ' . $council_shortname . ' MBE certificate is expiring tomorrow. Please upload your updated certificate at the earliest by clicking on the below button.</p>';
    }

    //$body = $this->mm365_email_body($title, $content, $link, 'Upload Certificate');
    $body = $this->mm365_email_body_template($title, $content, $link, 'Upload Certificate');
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($mail_id, $subject, $body, $headers);

  }





  /*------------------------------------------------------------
      Can Council manager access this post
  --------------------------------------------------------------*/
  function can_council_access($post_id, $user_council_id, $redirect_slug)
  {

    $post_belongs_to = get_post_meta($post_id, 'mm365_submitted_council', true);

    if ($post_belongs_to != $user_council_id) {
      wp_redirect(site_url() . "/" . $redirect_slug);
      exit;
    }

  }


  /*------------------------------------------------------------
  v2.3 Onwards   
  Generate certification report with selected parameters
  Self action page 
  --------------------------------------------------------------*/
  public function generate_report()
  {

    $from_date = $_REQUEST['from_date'];
    $to_date = $_REQUEST['to_date'];
    $council = $_REQUEST['council_filter'];
    $status = $_REQUEST['certificate_status'];

    //
    if (empty($from_date)) {
      $from_date = '01/01/1975';
    }
    if (empty($to_date)) {
      $to_date = date("m/d/Y");
    }

    //Include council to query
    if ($council != '') {
      $council_filter = array(
        'key' => 'mm365_submitted_council',
        'value' => $council,
        'compare' => '=',
      );
    } else {
      $council_filter = '';
    }

    if ($status != '') {
      $status_filter = array(
        'key' => 'mm365_certificate_status',
        'value' => $status,
        'compare' => '=',
      );
    } else {
      $status_filter = '';
    }

    $toDate = date_parse_from_format("m/d/Y", $to_date);

    //Main query
    $certificate_args = array(
      'posts_per_page' => -1,
      // No limit
      'post_type' => 'mm365_certification',
      'post_status' => array('publish'),
      'date_query' => array(
        'after' => $from_date,
        'before' => array(
          'year' => $toDate['year'],
          'month' => $toDate['month'],
          'day' => $toDate['day'],
        ),
        'inclusive' => true,
      ),
      'meta_query' => array(
        $status_filter,
        $council_filter
      )
    );

    $file_name = "Report - MBE certification";

    $data = array();
    $certificates_query = new \WP_Query($certificate_args);
    $found_results = $certificates_query->found_posts;
    //Check result count
    if ($found_results > 0) {
      while ($certificates_query->have_posts()):
        $certificates_query->the_post();
        $company = $this->replace_html_in_companyname(get_the_title(get_the_ID()));
        $date_uploaded = get_post_time("m/d/Y");
        $expiration_date = get_post_meta(get_the_ID(), 'mm365_expiry_date', true);
        $notes = get_post_meta(get_the_ID(), 'mm365_admin_note', true);
        $status = get_post_meta(get_the_ID(), 'mm365_certificate_status', true);

        //Get Council Details
        $council_id = get_post_meta(get_the_ID(), 'mm365_submitted_council', true);
        $council_short_name = get_post_meta($council_id, 'mm365_council_shortname', true);

        //Get company details
        $company_id = get_post_meta(get_the_ID(), 'mm365_submitted_by', true);
        $contact_person = get_post_meta($company_id, 'mm365_contact_person', true);
        $email = get_post_meta($company_id, 'mm365_company_email', true);
        $phone = get_post_meta($company_id, 'mm365_company_phone', true);

        $certificate_details = array(
          $company,
          $contact_person,
          $phone,
          $email,
          $council_short_name,
          $date_uploaded,
          $expiration_date,
          $notes ?: '-',
          ucfirst($status)
        );
        array_push($data, $certificate_details);
      endwhile;

      $writer_2 = new XLSXWriter();
      $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 50, 'wrap_text' => true);
      $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 20);
      $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');
      $writer_2->writeSheetHeader('Sheet1', array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string', '9' => 'string'), $col_options = ['widths' => [50, 30, 30, 50, 30, 30, 30, 30, 30], 'suppress_row' => true]);

      $writer_2->writeSheetRow('Sheet1', $rowdata = array($file_name), $styles1);

      $writer_2->writeSheetRow('Sheet1', $rowdata = array(
        'Submitted Company',
        'Contact Person',
        'Phone',
        'Email',
        'Council',
        'Date uploaded',
        'Expiration date',
        'Notes',
        'Status'
      ), $styles2);

      foreach ($data as $dat) {
        $writer_2->writeSheetRow('Sheet1', $dat, $styles3);
      }

      $file_2 = $file_name . '.xlsx';
      $writer_2->writeToFile($file_2);

      if (file_exists($file_2)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_2) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_2));
        readfile($file_2);
        unlink($file_2);
        exit;
      }
    } else {

      //Show error if no records 
      setcookie('report_generate_status_certification', 'err', time() + 3600, "/","");
      wp_redirect(site_url() . '/report-certification');

    }
  }


  /**---------------------------------------------------------------
   * Show certified badge
   * 
   -----------------------------------------------------------------*/
  function certified_badge($company_id, $return = FALSE)
  {

    if ($this->is_certified($company_id) == TRUE):

      $badge = '<img class="certified-logo" src="' . get_template_directory_uri() . '/assets/images/certified.svg" alt="">';

      if ($return == FALSE) {
        echo $badge;
      } else
        return $badge;

    endif;

  }


  /**
   * 
   * 
   * 
   */
  function quick_report_download($period = 'week', $status = NULL, $sa_council_filter = NULL)
  {

    $user = wp_get_current_user();

    //Check if council manager is reading the reports
    $council_id = $this->get_userDC($user->ID);

    //IF sa_council_filter is present ovveride councilid
    if ($sa_council_filter != NULL) {
      $council_id = $sa_council_filter;
    }
    //sacouncilfilter


    if ($council_id != '') {
      $council_filter = array(
        'key' => 'mm365_submitted_council',
        'value' => $council_id,
        'compare' => '=',
      );
      $council_shortname = $this->get_council_info($council_id) . " - ";
      $council_col_width = 0; //Hide council column for council managers
    } else {
      $council_filter = '';
      $council_shortname = '';
      $council_col_width = 30;
    }

    if ($status == 'pending'):
      $column = 'post_date';
    else:
      $column = 'post_modified';
    endif;

    $quickreports_certificate_args = array(
      'posts_per_page' => -1,
      // No limit
      'post_type' => 'mm365_certification',
      'post_status' => array('publish'),
      'date_query' => array(
        array('column' => $column, 'after' => '1 ' . $period . ' ago')
      ),
      'meta_query' => array(
        array(
          'key' => 'mm365_certificate_status',
          'value' => $status,
          'compare' => '=',
        ),
        $council_filter
      )
    );
    $file_name = "Report - " . $council_shortname . "certificates (" . $status . ") with in a " . $period;

    $data = array();
    $certificates_query = new \WP_Query($quickreports_certificate_args);

    while ($certificates_query->have_posts()):
      $certificates_query->the_post();
      $company = $this->replace_html_in_companyname(get_the_title(get_the_ID()));
      $date_uploaded = get_post_time("m/d/Y");
      $expiration_date = get_post_meta(get_the_ID(), 'mm365_expiry_date', true);
      $notes = get_post_meta(get_the_ID(), 'mm365_admin_note', true);
      $status = get_post_meta(get_the_ID(), 'mm365_certificate_status', true);

      //Get Council Details
      $council_id = get_post_meta(get_the_ID(), 'mm365_submitted_council', true);
      $council_short_name = get_post_meta($council_id, 'mm365_council_shortname', true);

      $certificate_details = array(
        $company,
        $council_short_name,
        $date_uploaded,
        $expiration_date,
        $notes ?: '-',
        ucfirst($status)
      );
      array_push($data, $certificate_details);
    endwhile;

    $writer_2 = new XLSXWriter();
    $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 50, 'wrap_text' => true);
    $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 20);
    $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');
    $writer_2->writeSheetHeader('Sheet1', array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string'), $col_options = ['widths' => [50, $council_col_width, 30, 50, 30], 'suppress_row' => true]);

    if ($council_id == ''):
      $writer_2->writeSheetRow('Sheet1', $rowdata = array($file_name, 'From ' . date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))) . "-1 " . $period)) . ' To ' . date('m/d/Y', time())), $styles1);
    else:
      $writer_2->writeSheetRow('Sheet1', $rowdata = array($file_name . '. From ' . date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))) . "-1 " . $period)) . ' To ' . date('m/d/Y', time())), $styles1);
    endif;

    $writer_2->writeSheetRow('Sheet1', $rowdata = array(
      'Submitted by',
      'Council',
      'Date uploaded',
      'Expiration date',
      'Notes',
      'Status'
    ), $styles2);

    foreach ($data as $dat) {
      $writer_2->writeSheetRow('Sheet1', $dat, $styles3);
    }

    $file_2 = $file_name . '.xlsx';
    $writer_2->writeToFile($file_2);

    if (file_exists($file_2)) {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="' . basename($file_2) . '"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file_2));
      readfile($file_2);
      unlink($file_2);
      exit;
    }


  }


  //Class ends here
}
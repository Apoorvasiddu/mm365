<?php
namespace Mm365;


trait CertificateAddon
{


  /**
   * @param int $company_id
   * @return bool
   */
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


  /**
   * @param int $company_id
   * @param bool $return 
   * @return mixed
   */
  function get_certified_badge($company_id, $return = FALSE)
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
   * @param int $expire_in
   * 
   *
   * 
   * S1 - Find all certificates expiring in 90 days
   * s2 - Find all cerficates expiring in 60 days
   * s3 - Find all cerficates expiring in 30 days
   * s4 - Find all cerficates expiring in 0 days (tommorow)
   * s5 - Put certificates to expired status if expiry date is greater than current
   */


  function notify_expiring_certificates($expire_in = '90')
  {


    //Find certificates expiring in 90th day from today
    $today = date('Y-m-d');
    switch ($expire_in) {
      case '0':
        $expiring = (strtotime($today) + 1440 * 60);
        break;
      case '60':
        $expiring = (strtotime($today) + 86400 * 60);
        break;
      case '30':
        $expiring = (strtotime($today) + 43200 * 60);
        break;
      default:
        $expiring = (strtotime($today) + 129600 * 60);
        break;
    }

    $notify = array();
    $args = array(
      'post_type' => 'mm365_certification',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'order' => 'DESC',
      'meta_query' => array(
        array(
          'key' => 'mm365_expiry_date_timestamp',
          'value' => $expiring,
          'compare' => '=',
        ),
        array(
          'key' => 'mm365_certificate_status',
          'value' => 'verified',
          'compare' => '=',
        )

      )
    );

    $certificates = new \WP_Query($args);

    if ($certificates->have_posts()) {
      while ($certificates->have_posts()) {
        $certificates->the_post();
        $company_id = get_post_meta(get_the_ID(), 'mm365_submitted_by', true);
        $council_id = get_post_meta(get_the_ID(), 'mm365_submitted_council', true);
        $mail_id = get_post_meta($company_id, 'mm365_company_email', true);
        $contact_name = get_post_meta($company_id, 'mm365_contact_person', true);

        $this->notification_mail($mail_id, $contact_name, $expire_in, $council_id);
      }
      return $notify;
    } else
      return false;

  }

  /**
   * Change certificate status to expired
   * 
   * 
   */
  function put_to_expire()
  {

    $today = date('Y-m-d');

    $args = array(
      'post_type' => 'mm365_certification',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'order' => 'DESC',
      'meta_query' => array(
        array(
          'key' => 'mm365_expiry_date_timestamp',
          'value' => strtotime($today),
          'compare' => '<=',
        ),
        array(
          'key' => 'mm365_certificate_status',
          'value' => 'verified',
          'compare' => '=',
        )

      )
    );

    $certificates = new \WP_Query($args);
    if ($certificates->have_posts()) {
      while ($certificates->have_posts()) {
        $certificates->the_post();

        //Get company id
        $company_id = get_post_meta(get_the_ID(), 'mm365_submitted_by', true);
        //Remove 
        if (metadata_exists('post', $company_id, 'mm365_certification_status')) {
          delete_post_meta($company_id, 'mm365_certification_status', 'verified');
        }

        $post_information = array(
          'ID' => get_the_ID(),
          'meta_input' => array(
            'mm365_certificate_status' => 'expired',
          ),
          'post_type' => 'mm365_certification',
        );
        $post_id = wp_update_post($post_information);

      }
    }
    wp_reset_query();

  }

  /**
   * Notify expiry of a certificate
   * 
   * 
   */
  function notify_expiry()
  {

    //bulk mailing
    $today = date('Y-m-d');
    $args = array(
      'post_type' => 'mm365_certification',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'order' => 'DESC',
      'meta_query' => array(
        array(
          'key' => 'mm365_expiry_date_timestamp',
          'value' => strtotime($today),
          'compare' => '=',
        ),
        array(
          'key' => 'mm365_certificate_status',
          'value' => 'expired',
          'compare' => '=',
        )

      )
    );

    $certificates = new \WP_Query($args);
    if ($certificates->have_posts()) {
      while ($certificates->have_posts()) {
        $certificates->the_post();

        $company_id = get_post_meta(get_the_ID(), 'mm365_submitted_by', true);
        $mail_id = get_post_meta($company_id, 'mm365_company_email', true);
        $contact_name = get_post_meta($company_id, 'mm365_contact_person', true);
        $submited_council_id = get_post_meta(get_the_ID(), 'mm365_submitted_council', true);
        $council_shortname = get_post_meta($submited_council_id, 'mm365_council_shortname', true);

        //Mail
        $link = site_url() . '/certificate-verification/';
        $subject = 'Your ' . $council_shortname . ' MBE certificate has expired!';
        $title = 'Your ' . $council_shortname . ' MBE certificate has expired!';
        $content = '
        <p>Hi ' . $contact_name . ',</p>
        <p>Your ' . $council_shortname . ' MBE certificate has expired! Please upload your updated certificate at the earliest by clicking on the below button.</p>';

        $body = $this->mm365_email_body($title, $content, $link, 'Upload Certificate');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($mail_id, $subject, $body, $headers);
      }
    }
    wp_reset_query();

  }


  /**
   * 
   * 
   * 
   */
  function admin_action_notices($mail_id, $contact_name, $message, $action, $council_id = NULL)
  {

    $link = site_url() . '/certificate-verification/';

    //Get council short name
    if ($council_id != ''):
      $council_shortname = " " . get_post_meta($council_id, 'mm365_council_shortname', true) . " ";
    else:
      $council_shortname = ' ';
    endif;


    switch ($action) {
      case 'unapproved':
        $subject = 'Your' . $council_shortname . 'MBE certificate is unapproved';
        $title = 'Your' . $council_shortname . 'MBE Certificate is unapproved';
        $content = '
      <p>Hi ' . $contact_name . ',</p>
      <p>Your' . $council_shortname . 'MBE certificate submitted for verification is unapproved.</p>
      <p>Reason:<br/>' . $message . '<p><p>Please click on the below button and login to view the certificate status.</p>';
        break;

      default:
        $subject = 'Your' . $council_shortname . 'MBE certificate is verified';
        $title = 'Your' . $council_shortname . 'MBE Certificate is verified';
        $content = '
      <p>Hi ' . $contact_name . ',</p>
      <p>Your' . $council_shortname . 'MBE certificate submitted for verification has been verified.</p>
      <p>Notes/Remarks:<br/>' . $message . '<p><p>Please click on the below button and login to view the certificate status.</p>';
        break;
    }

    $body = $this->mm365_email_body($title, $content, $link, 'Certificates');
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($mail_id, $subject, $body, $headers);

  }


  /**
   * 
   * 
   * 
   * 
   */

  /*------------------------------------------------------------
   * Admin notify pending certificates submitted
   * v1.6  onwards revised for 
   * Method to find emails of council managers and
   * Send the count of pending certificates for each council
   *
--------------------------------------------------------------*/
  function admin_notify_pending_ceritifcates()
  {


    //Get Council IDS
    $args = array(
      'post_type' => 'mm365_msdc',
      'posts_per_page' => -1,
      'orderby' => 'date',
      'fields' => 'ids'
    );
    $loop = new \WP_Query($args);
    $councils_list = array();
    while ($loop->have_posts()):
      $loop->the_post();
      $councils_list[] = get_the_ID();
    endwhile;
    wp_reset_postdata();


    //get council managers list
    $get_council_managers = get_users(array('role__in' => array('council_manager')));
    foreach ($get_council_managers as $user) {
      $cm[$user->user_email] = apply_filters('mm365_helper_get_usercouncil', $user->ID);
    }


    //Loop through council ids and find count of pending certificates
    $counts_and_emails = array();
    foreach ($councils_list as $council_id) {

      //Count of pending on each council
      $args = array(
        'post_type' => 'mm365_certification',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'DESC',
        'meta_query' => array(
          array(
            'key' => 'mm365_certificate_status',
            'value' => 'pending',
            'compare' => '=',
          ),
          array(
            'key' => 'mm365_submitted_council',
            'value' => $council_id,
            'compare' => '=',
          ),

        )
      );
      $certificates = new \WP_Query($args);
      $count_pending_certificates = $certificates->found_posts;

      //Email ids to send per council
      $emails = implode(",", array_keys(array_intersect($cm, [$council_id])));

      if ($count_pending_certificates > 0 && $emails != '') {
        $counts_and_emails[$council_id] = array($count_pending_certificates, $emails);
      }

    }

    //Emailing the details
    $link = site_url() . '/certificate-verification/';
    foreach ($counts_and_emails as $key => $value) {

      //$key- council_id
      //$value[0] - Count
      //$value[1] - emails comma seperated

      $council_shortname = get_post_meta($key, 'mm365_council_shortname', true);

      $subject = 'You have ' . $value[0] . ' new MBE certificates to verify';
      $title = $subject;
      $content = '<p>Hi ' . $council_shortname . ' Council Manager,</p>
                <p>There are ' . $value[0] . ' new certificates in Matchmaker365 waiting for verification. Please click on the below button to view and verify the certificates.</p>';

      $body = $this->mm365_email_body($title, $content, $link, 'Certificate Verification');
      $headers = array('Content-Type: text/html; charset=UTF-8');
      wp_mail($value[1], $subject, $body, $headers);

    }


  }


}
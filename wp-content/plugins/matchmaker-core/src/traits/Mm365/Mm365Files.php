<?php
namespace Mm365;

/**
 * All the supporting functions for companies
 * 
 * 
 */

trait Mm365Files
{


/**
     * File Upload support methods
     * Uploads all capability statements to custom folder 'mm365'
     * 
     */

    function insert_attachment($file_handler,$setthumb='false') {

        // check to make sure its a successful upload
        if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();
      
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        require_once(ABSPATH . "wp-admin" . '/includes/file.php');
        require_once(ABSPATH . "wp-admin" . '/includes/media.php');
      
        add_filter( 'upload_dir', array($this, 'mm365_custom_upload_dir') );
        $attach_id = media_handle_upload( $file_handler,0 );
        remove_filter( 'upload_dir', array($this, 'mm365_custom_upload_dir') );
        return $attach_id;
      
    } 
      
    function mm365_custom_upload_dir( $dir_data ) {
     
      $custom_dir = 'mm365';
      return [
          'path' => $dir_data[ 'basedir' ] . '/' . $custom_dir,
          'url' => $dir_data[ 'url' ] . '/' . $custom_dir,
          'subdir' => '/' . $custom_dir,
          'basedir' => $dir_data[ 'error' ],
          'error' => $dir_data[ 'error' ],
      ];
    }
}
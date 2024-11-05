<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://miniorange.com/
 * @since      1.1.1
 *
 * @package    Media_Restriction
 * @subpackage Media_Restriction/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.1.1
 * @package    Media_Restriction
 * @subpackage Media_Restriction/includes
 * @author     test <test@test.com>
 */
class Media_Restriction_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.1.1
	 */
	public static function deactivate() {
		delete_option( 'mo_media_restriction_admin_email' );
		delete_option( 'mo_media_restriction_admin_customer_key' );
		delete_option( 'mo_media_restriction_new_user' );

		$home_path     = get_home_path();
		$htaccess_file = $home_path . '.htaccess'; // Specify the path to your .htaccess file.

		// Read the existing .htaccess file content.
		$existing_content = file_get_contents( $htaccess_file );

		// Remove the content between BEGIN and END MINIORANGE MEDIA RESTRICTION.
		$updated_content = preg_replace( '/# BEGIN MINIORANGE MEDIA RESTRICTION(.*?)# END MINIORANGE MEDIA RESTRICTION/ms', '', $existing_content );
		// Write the updated content back to the .htaccess file.
		file_put_contents( $htaccess_file, $updated_content );
	}

}

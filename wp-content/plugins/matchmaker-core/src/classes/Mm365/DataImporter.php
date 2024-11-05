<?php

namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class DataImporter {

    use CouncilAddons;

	const VERSION                    = '1.0';

	public function __construct() {

		// Add styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ), 11 );
		add_action( 'wp_ajax_import_csv_data', array( $this, 'import_csv_data' ) );
		add_action( 'wp_ajax_upload_csv_file', array( $this, 'upload_csv_file' ) );
		add_action( 'wp_ajax_update_importlog', array( $this, 'update_importlog' ) );

		add_filter( 'mm365_dataimport_form', array( $this, 'matchmaker_365_import_ui' ) );

	}


	public function enqueue_scripts_and_styles(  ) {

		//if ( $hook == "toplevel_page_matchmaker-365-import" ) {

			if ( wp_register_script( 'matchmaker-365-importer-scripts', plugins_url('matchmaker-core/assets/matchmaker-365-scripts.js'), array( 'jquery' ), self::VERSION, TRUE ) ) {
				wp_enqueue_script( 'matchmaker-365-importer-scripts' );
				wp_localize_script( 'matchmaker-365-importer-scripts', 'ajax_object', array(
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'plugin_url' => plugin_dir_url( __FILE__ )
				) );
			}

			if ( wp_register_style( 'matchmaker-365-importer-styles', plugins_url('matchmaker-core/assets/matchmaker-365-styles.css'), array(), self::VERSION ) ) {
				wp_enqueue_style( 'matchmaker-365-importer-styles' );
			}

		
		//}
	}

	public function matchmaker_365_import_ui() {
		$this->database_import_form();
	}

	// Adds import form to page
	public function database_import_form() {
		$this->output_layouts();
		$this->form_layout();
		
	}

	// Output layouts
	public function output_layouts() {
		// <a class="error-download">Download reports</a>
		echo '<div class="form-group form-row" id="process" style="display:none;">
		<div class="import-progress">
			  <span class="import-progress-bar" style="width: 0%"></span>
		</div>
	  </div>';

		echo "<div id='output-container' style='display :none'>" ;
		 echo '<div id="status-messages-import"><h4>Processed</h4><div class="content"></div></div>';
		 echo '<div id="error-messages-import"><h4>Failed</h4><div class="content"></div></div>';
		 echo '<div id="success-messages-import"><h4>Imported</h4><div class="content"></div></div>';
		echo "</div>" ;
	}

	// New Form Layout
	public function form_layout() {

		echo '<span id="message"></span>
					<form id="sample_form" method="POST" enctype="multipart/form-data" data-parsley-validate class="form-horizontal">
					    <div class="row form-group">
						  <div class="col-lg-6" >
							<div data-intro="Council associated with the companies being imported"><label class="control-label">Council<span>*</span><br/><small>Which the importing companies belong to</small></label>
							<select data-parsley-errors-container=".council-parError" name="council_id" id="importing_council_id" class="form-select form-control mm365-single" aria-label="Default select example" required>
							<option value="">-select-</option>';							   
							   foreach($this->get_councils_list() as $key => $value){
									echo '<option value="'.$key.'">'.$value[0].'</option>';
							   }
						echo '</select><div class="council-parError"></div> </div>';

						echo '<div data-intro="Select the type of companies being imported"><label class="control-label pto-10"  for="">Service Type<span>*</span></label><br/>
						<input type="radio" name="service_type"  value="buyer"> Buyer
						&nbsp;<input type="radio" name="service_type"  value="seller" checked> Supplier</div>';

						echo '
                             <div data-intro="Select the country to which the companies belong to. The address validation is done based on the country selected by the importer">
							<label class="control-label pto-10" for="">Country<span>*</span><small><br/>Which the importing companies are from</small></label>
							<select data-parsley-errors-container=".country-parError" required name="company_country" id="importing_to_country" required class="cmp_country form-control mm365-single" data-parsley-errors-container=".countryError">
								<option value="">-Select-</option>';
						
								$country_list = $this->mm365_importer_countries_list();
									foreach ($country_list as $key => $value) {   
									if($value->id == '233'): $default_country = "selected"; else: $default_country = ''; endif;
										echo "<option ".$default_country." value='".$value->id."' >".$value->name."</option>";
									}
							
							echo '</select>
							<div class="country-parError"></div> </div>' ;

							echo '<div data-intro="Drag and drop the CSV file to import. The CSV file  should comply with the standards mentioned on the instructions shown on the right hand side of this page. You can optionally download a sample CSV file"><label class="control-label pto-10" for="">CSV File<span>*</span><small><br/>Please read the instructions</small></label>
									<div class="importer-file-upload">
									<div class="image-upload-wrap">
										<input  data-parsley-errors-container=".csv-parError" required id="importer-file-upload-input" name="file" class="importer-file-upload-input" type=\'file\'  accept=".csv, .CSV" />
										<div class="drag-text">
											<h3>Drag and drop a .CSV file <br/>or click \'Add CSV\'</h3>
										</div>
									</div>

									<div class="importer-file-upload-content">							  
										<div class="image-title-wrap">
											<button id="importer-file-upload-remove" type="button" class="remove-image">Remove \'<span class="image-title">Uploaded Image</span>\'</button>
										</div>
									</div>

								<button class="importer-file-upload-btn" type="button" onclick="jQuery(\'.importer-file-upload-input\').trigger( \'click\' )">Add CSV</button>
								<div class="csv-parError"></div> </div>

								</div>
								
								
							<div class="form-group" data-intro="Click to start importing">						
							<input type="hidden" name="hidden_field" value="1" />
							<input type="submit" name="import" id="import" class="btn btn-primary" value="Import Data" />
						    </div>

								
								
								';
						echo '</div>';

						echo '<div class="col-lg-6">
						
						<h5>Preparing .CSV File for import</h5>
						<p>The CSV file uploading should have columns mentioned below.</p>
						<div class="row">
						  <div class="col-md-5" data-intro="The CSV should have  all the mandatory columns  mentioned in the list ">
							   <h6><span class="red">*</span> Mandatory columns</h6>
							   <ul>
								   <li data-hint="Unique numeric ID" data-hint-position="top-left">id</li>
								   <li data-hint="Name of the company" data-hint-position="top-left">name</li>
								   <li>Address1</li>
								   <li>phone</li>
								   <li>city</li>
								   <li>state</li>
								   <li>OwnerFirstName</li>
								   <li>OwnerLastName</li>
								   <li>OwnerPhone</li>
								   <li>OwnerEmail</li>
								   <li>ProductDescription</li>
							   </ul>
						  </div>
						  <div class="col-md-7" data-intro="The CSV can import values from these columns. However they are NOT mandatory">
							   <h6>Optional columns</h6>
							   <ul>
								   <li>NumberOfEmployeesFulltime</li>
								   <li>AnnualSales</li>
								   <li>MinorityClassificationCode</li>
								   <li>WebAddress</li>
								   <li>naics</li>
								   <li>MajorCustomers</li>
								   <li>zip</li>
								   <li>OwnerTitle</li>
							   </ul>
						  </div>
						</div>
						<p>Please adhere to the column names mentioned above, else the importer will reject the file stating \'CSV file is not valid\'. Having other columns in the CSV file is acceptable
						but those will not be imported. The order of columns are not important.</p>
					   
						<p><a data-intro="Sample CSV file for importing companies" class="font-weight-bold" href="'.get_template_directory_uri().'/assets/csv/mm365_import_template.csv" download title="Sample CSV">Download Sample .CSV file</a></p>
			
						 <div data-intro="Things to be aware of while system is importing the records">
						    <h5>Things to know before importing</h5>
						    <ul class="text-left">
								<li>Please do not close this window while importing</li>
								<li>Do not let the system sleep while importing</li>
								<li>Make sure the CSV file is complying the required guidelines </li>
								<li>If there are duplicate emails, the newly imported company will be added as new company to existing user who is using the email address</li>
								<li>Make sure the council choice is correct</li>
								<li>Make sure the country choice is correct</li>
								<li>The record set should not contain records from multiple countries</li>
								<li>After import, a log will be displayed stating the status of each record</li>
							</ul>
                         </div>
						</div>';

                       echo '</div>

					</form>
					';
	}



	public function import_csv_data() {
		// sleep( 5 );

		//Council data
        $council_id = $_REQUEST['council'];

		//Country ID
		$company_country_id = $_REQUEST['company_country'];

		//Service TYPE
		$company_service_type = $_REQUEST['service_type'];

		// Use first row as key
		$firstKey = array_key_first( $_REQUEST['item'] );

		if ( isset( $_REQUEST['last_item'] ) ) {
			echo "<div class='last_item'>1</div>";
		}

		// Validation for user name and email
		if ( !$_REQUEST['item']['OwnerFirstName'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Owner first name cannot be empty' );
		} else if ( !$_REQUEST['item']['OwnerLastName'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Owner last name cannot be empty' );
		} else if ( !$_REQUEST['item']['OwnerEmail'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Owner email cannot be empty' );
		}

		// Validations for mandatory fields
		if ( !$_REQUEST['item']['name'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Name cannot be empty' );
		}
		if ( !$_REQUEST['item']['Address1'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Address1 cannot be empty' );
		}
		if ( !$_REQUEST['item']['city'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Owner first name cannot be empty' );
		}
		if ( !$_REQUEST['item']['state'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'City cannot be empty' );
		}
		if ( !$_REQUEST['item']['phone'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Phone cannot be empty' );
		}
		// if ( !$_REQUEST['item']['WebAddress'] ) {
		// 	$this->return_error( $_REQUEST['item'][$firstKey], 'WebAddress cannot be empty' );
		// }
		if ( !$_REQUEST['item']['OwnerPhone'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'OwnerPhone cannot be empty' );
		}
		if ( !$_REQUEST['item']['OwnerEmail'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'OwnerEmail cannot be empty' );
		}
		if ( !$_REQUEST['item']['ProductDescription'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'ProductDescription cannot be empty' );
		}

		// Fetch codes from the database
		$codes = $this->fetch_codes( $_REQUEST['item']['state'], $_REQUEST['item']['city'] ,$company_country_id);
		if ( empty( $codes ) ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Something went wrong in fetching codes' );
		} else {
			if ( !$codes[0]['city_id'] ) {
				$this->return_error( $_REQUEST['item'][$firstKey], 'City not found' );
			}
			if ( !$codes[0]['state_id'] ) {
				$this->return_error( $_REQUEST['item'][$firstKey], 'State not found' );

			}
			if ( !$codes[0]['country_id'] ) {
				$this->return_error( $_REQUEST['item'][$firstKey], 'Country not found' );
			}
		}

		if ( !$_REQUEST['item']['OwnerFirstName'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Owner first name cannot be empty' );
		}
		if ( !$_REQUEST['item']['OwnerFirstName'] ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Owner first name cannot be empty' );
		}

		// No of employees calculation
		$no_of_employees = $this->calculate_employee_range( $_REQUEST['item']['NumberOfEmployeesFulltime'] );

		// Size of company
		$size_of_company = $this->calculate_size_of_company( $_REQUEST['item']['AnnualSales'] );

		//Phone remove () and -
		$phone       = str_replace( array( '(', ')', ' ', '-', '+' ), '', $_REQUEST['item']['phone'] );
		$owner_phone = str_replace( array( '(', ')', ' ', '-', '+' ), '', $_REQUEST['item']['OwnerPhone'] );

		// Phone validation
		preg_match_all( '/^([0-9]{5,15}\d)$/', $phone, $phone_valid );
		if ( empty( $phone_valid ) ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Phone is invalid' );
		}

		preg_match_all( '/^([0-9]{5,15}\d)$/', $owner_phone, $owner_phone_valid );
		if ( empty( $owner_phone_valid ) ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Owner Phone is invalid' );
		}

		// Email validation
		$email = $this->test_input( $_REQUEST['item']['OwnerEmail'] );
		if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$this->return_error( $_REQUEST['item'][$firstKey], 'Invalid email format' );
		}

		// Naics codes formating
		$naics = $_REQUEST['item']['naics'];
		if ( stripos( $naics, '<NL>' ) !== FALSE ) {
			$naics = preg_replace( '/<NL><NL>+/', '', $naics );
			if ( stripos( $naics, '<NL>' ) !== FALSE ) {
				$naics = array_filter( explode( "<NL>", $naics ) );
			}
			//$naics = json_encode( $naics );
		}

		$major_customers = json_encode( explode( ",", trim( $_REQUEST['item']['MajorCustomers'] ) ) );


		// Registration
		$user_name = $this->make_username($_REQUEST['item']['OwnerFirstName'],$_REQUEST['item']['OwnerLastName'], $_REQUEST['item'][$firstKey]);
		//$user_pass = $this->randomPassword();
		$full_name = trim( $_REQUEST['item']['OwnerFirstName'] ) . ' ' . trim( $_REQUEST['item']['OwnerLastName'] );

		/**
		 * 
		 * Check if the email in importing record has already been registered
		 * in that case add the company to that user, else create a new user 
		 * and add the company to the newly created user
		 * 
		 */
		$existing_user_data = get_user_by('email', $email);
		$existing_user_id = $existing_user_data->ID;

		if(!$existing_user_id){

			$userdata = array(
				'user_pass'    => wp_generate_password(8),
				'user_login'   => $user_name,
				'user_email'   => $email,
				'display_name' => $full_name,
				'nickname'     => $full_name,
				'first_name'   => $_REQUEST['item']['OwnerFirstName'],
				'last_name'    => $_REQUEST['item']['OwnerLastName'],
			);
			$user_id  = wp_insert_user( $userdata );
			
		}else{
			$user_id  = $existing_user_id;
		}

		if ( is_wp_error( $user_id ) ) {
			$error = $user_id->get_error_message();
			$this->return_error( $_REQUEST['item'][$firstKey], $error );
		}

		// Add Post
		$company_title = str_replace("&amp;", "&", $_REQUEST['item']['name']);
		$my_post = array(
			'post_title'  => wp_strip_all_tags($company_title),
			'post_status' => 'publish',
			'post_author' => $user_id,
			'post_type'   => 'mm365_companies'
		);

		// Insert the post into the database.
		$post_id = wp_insert_post( $my_post );

		// Add Post metadata here and test
		update_post_meta( $post_id, 'mm365_company_address', $_REQUEST['item']['Address1'] );
		update_post_meta( $post_id, 'mm365_company_city', $codes[0]['city_id'] );
		update_post_meta( $post_id, 'mm365_company_state', $codes[0]['state_id'] );
		update_post_meta( $post_id, 'mm365_company_country', $company_country_id );
		update_post_meta( $post_id, 'mm365_zip_code', $_REQUEST['item']['zip'] );
		update_post_meta( $post_id, 'mm365_company_phone', $phone_valid[0][0] );
		update_post_meta( $post_id, 'mm365_website', $_REQUEST['item']['WebAddress'] );
		update_post_meta( $post_id, 'mm365_contact_person', $_REQUEST['item']['OwnerFirstName'] . ' ' . $_REQUEST['item']['OwnerLastName'] );
		update_post_meta( $post_id, 'mm365_alt_contact_person', $_REQUEST['item']['OwnerTitle'] );
		update_post_meta( $post_id, 'mm365_alt_phone', $owner_phone_valid[0][0] );
		update_post_meta( $post_id, 'mm365_company_email', $email );
		update_post_meta( $post_id, 'mm365_number_of_employees', $no_of_employees );
		update_post_meta( $post_id, 'mm365_service_type',  $company_service_type );
		update_post_meta( $post_id, 'mm365_minority_category', $_REQUEST['item']['MinorityClassificationCode'] );
		update_post_meta( $post_id, 'mm365_company_name' , $_REQUEST['item']['name'] );
		//update_post_meta( $post_id, 'mm365_service_type', $company_service_type);


		//update_post_meta( $post_id, 'mm365_naics_codes', $naics );
		foreach( $naics as $value ) {
			add_post_meta( $post_id, 'mm365_naics_codes', $value );
		}

		update_post_meta( $post_id, 'mm365_company_description', $_REQUEST['item']['ProductDescription'] );
		update_post_meta( $post_id, 'mm365_main_customers', $major_customers );
		update_post_meta( $post_id, 'mm365_size_of_company', $size_of_company );
		update_post_meta( $post_id, 'mm365_company_council', $council_id );
		

		echo "<div class='csv_row_id'>" . $_REQUEST['item'][$firstKey] . "</div>";
		echo "<div class='csv_email'>" . $email . "</div>";
		echo "<div class='csv_company_id'>" . $post_id . "</div>";
		echo "<div class='status_code'>0</div>";
		wp_die();
	}

	public function return_error( $csv_key = '', $error_msg = '' ) {
		echo "<div class='csv_row_id'>$csv_key</div>";
		echo "<div class='status_code'>2</div>";
		echo "<div class='message'>$error_msg</div>";
		wp_die();
	}

	public function fetch_codes( $state_code, $city_name ,$country_id) {
		global $wpdb;
		$sql     = "SELECT `id` as `city_id`,`state_id`,`country_id` FROM `".$wpdb->prefix."cities` WHERE `state_code` = '$state_code' and `name` = '$city_name' and `country_id` = '$country_id'";
		$results = $wpdb->get_results( $sql, ARRAY_A );
		return $results;
	}

	public function calculate_employee_range( $no_of_employees ) {
		if ( $no_of_employees < 20 ) {
			return '&lt; 20';
		} else if ( $no_of_employees < 20 ) {
			return '&lt; 20';
		} else if ( ( $no_of_employees > 20 ) && ( $no_of_employees < 50 ) ) {
			return '20 to 50';
		} else if ( ( $no_of_employees > 50 ) && ( $no_of_employees < 100 ) ) {
			return '50 to 100';
		} else if ( ( $no_of_employees > 100 ) && ( $no_of_employees < 200 ) ) {
			return '100 to 200';
		} else if ( ( $no_of_employees > 200 ) && ( $no_of_employees < 500 ) ) {
			return '200 to 500';
		} else if ( ( $no_of_employees > 500 ) && ( $no_of_employees < 1000 ) ) {
			return '500 to 1000';
		} else {
			return '1000+';
		}
	}

	public function calculate_size_of_company( $size ) {
		if ( $size < 100000 ) {
			return '&lt;$100,000';
		} else if ( ( $size > 100000 ) && ( $size < 500000 ) ) {
			return '$100,000 - $500,000';
		} else if ( ( $size > 500000 ) && ( $size < 1000000 ) ) {
			return '$500,000 - $1M';
		} else if ( ( $size > 1000000 ) && ( $size < 5000000 ) ) {
			return '$1M - $5M';
		} else if ( ( $size > 5000000 ) && ( $size < 50000000 ) ) {
			return '$5M - $50M';
		} else if ( ( $size > 50000000 ) && ( $size < 200000000 ) ) {
			return '$50M - $200M';
		} else if ( ( $size > 200000000 ) && ( $size < 500000000 ) ) {
			return '$200M - $500M';
		} else if ( ( $size > 500000000 ) && ( $size < 1000000000 ) ) {
			return '$500M - $1B';
		} else {
			return '$1B+';
		}
	}


	public function upload_csv_file() {
		sleep( 5 );
		if ( isset( $_POST['hidden_field'] ) ) {
			$error      = '';
			$total_line = '';

			if ( $_FILES['file']['name'] != '' ) {
				$allowed_extension = array( 'csv' );
				$file_array        = explode( ".", $_FILES["file"]["name"] );
				$extension         = end( $file_array );
				if ( in_array( $extension, $allowed_extension ) ) {
					$new_file_name             = rand() . '.' . $extension;
					$_SESSION['csv_file_name'] = $new_file_name;
					$uploads_path              = WP_PLUGIN_DIR . '/matchmaker-core/dataimports/';
					move_uploaded_file( $_FILES['file']['tmp_name'], $uploads_path . $new_file_name );
					$file_content = file( $uploads_path . $new_file_name, FILE_SKIP_EMPTY_LINES );
					$total_line   = count( $file_content );
				} else {
					$error = 'Only CSV file format is allowed';
				}
			} else {
				$error = 'Please Select File';
			}

			if ( $error != '' ) {
				$output = array(
					'error' => $error
				);
			} else {
				$arrayFromCSV = $this->csv_to_array( $uploads_path . $new_file_name );
				if ( empty( $arrayFromCSV ) ) {
					$output = array(
						'error' => 'CSV file is empty'
					);
				} else {
					$output = array(
						'success'     => TRUE,
						'total_line'  => ( $total_line - 1 ),
						'file_url'    => WP_PLUGIN_DIR . '/matchmaker-core/dataimports/'. $new_file_name,
						'csv_content' => $arrayFromCSV,
						'council_id'  => $_POST['council_id'],
						'company_country_id'  => $_POST['company_country'],
						'service_type'  => $_POST['service_type']
					);
				}
			}

			echo json_encode( $output );
		}
		wp_die();
	}

	public function csv_to_array( $filename = '', $delimiter = ',' ) {
		if ( !file_exists( $filename ) || !is_readable( $filename ) ) return FALSE;

		$header      = NULL;
		$data        = array();
		$used_fields = array(
			'OwnerFirstName',
			'OwnerLastName',
			'OwnerEmail',
			'name',
			'Address1',
			'city',
			'state',
			'phone',
			'WebAddress',
			'OwnerPhone',
			'ProductDescription',
			'NumberOfEmployeesFulltime',
			'AnnualSales',
			'naics',
			'MajorCustomers',
			'zip',
			'OwnerTitle',
			'MinorityClassificationCode'
		);

		if ( ( $handle = fopen( $filename, 'r' ) ) !== FALSE ) {
			while ( ( $row = fgetcsv( $handle, 10000, $delimiter ) ) !== FALSE ) {
				if ( !$header ) {
					$header = $row;
					if ( !empty( array_diff( $used_fields, $header ) ) ) {
						$output = array(
							'error' => 'CSV file is not valid'
						);
						echo json_encode( $output );
						wp_die();
					}
				} else {
					if ( empty( $row[0] ) ) continue;
					$data[] = array_combine( $header, $row );
				}
			}
			fclose( $handle );
		}
		return $data;
	}

	public function test_input( $data ) {
		$data = trim( $data );
		$data = stripslashes( $data );
		$data = htmlspecialchars( $data );
		return $data;
	}


	//User name
	function make_username($fname,$lname,$key){
		$clean_fname = preg_replace('/[^a-zA-Z0-9-_]/','', $fname);
		$clean_lname = preg_replace('/[^a-zA-Z0-9-_]/','', $lname);
		return strtolower($clean_fname."_".$clean_lname."_".$key)."<br/>";
	}

	//GET COUNCIL IDS
	function council_list(){
		$args = array(  
            'post_type' => 'mm365_msdc',
            'posts_per_page' => -1, 
            'orderby' => 'title', 
			'order' => 'ASC',
            'fields' => 'ids', 
        );
        $councils = new \WP_Query( $args );  
        while ( $councils->have_posts() ) : $councils->the_post(); 
          echo '<option value="'.get_the_ID().'">'.get_post_meta(get_the_ID(), 'mm365_council_shortname', true).' - '.get_the_title(get_the_ID()).'</option>';
        endwhile;
        wp_reset_postdata();
        
	}

	//
	function mm365_importer_countries_list(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT `id`,`name` FROM ".$wpdb->prefix."countries");
		return $result;
	}

	//Update Import LOG
	function update_importlog(){
		$current_user = wp_get_current_user();
		//GET email ids
		$success_emails = json_decode(stripslashes($_POST['emails']));
		$success_cmp_ids = json_decode(stripslashes($_POST['success_ids']));
		$failed_record_ids = json_decode(stripslashes($_POST['failed_companies']));
		$council_id = $_POST['council_id'];
		$country = $_POST['country'];

        $emails_imported =  maybe_serialize($success_emails);

		//Create post
		// Add Post
		$my_post = array(
			'post_title'  => "Imported - ".time(),
			'post_status' => 'publish',
			'post_author' => $current_user->ID,
			'post_type'   => 'mm365_importlog'
		);

		// Insert the post into the database.
		$post_id = wp_insert_post( $my_post );
		update_post_meta( $post_id, 'mm365_emails_imported', $emails_imported);
		update_post_meta( $post_id, 'mm365_companies_imported', maybe_serialize($success_cmp_ids));
		update_post_meta( $post_id, 'mm365_failed_records', maybe_serialize($failed_record_ids));
		update_post_meta( $post_id, 'mm365_imported_to_council', $council_id);
		update_post_meta( $post_id, 'mm365_imported_country', $country);
		update_post_meta( $post_id, 'mm365_imported_by', $current_user->ID);
		echo $post_id;
		wp_die();

	}

}

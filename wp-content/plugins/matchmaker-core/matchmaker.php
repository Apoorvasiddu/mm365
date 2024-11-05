<?php

use Mm365\CompanyReports;
use Mm365\ConsolidatedReport;
use Mm365\HelpPageManagement;
use Mm365\MatchrequestClosure;
use Mm365\MatchrequestsReports;
use Mm365\OfflineConferences;
use Mm365\PublicFacing;
use Mm365\ScheduledTasks;
use Mm365\Subscription;
use Mm365\SuperBuyers;
use Mm365\Helpers;
use Mm365\Council;
use Mm365\SuppliersAppearedinSearchReport;
use Mm365\UserDashboard;
use Mm365\Companies;
use Mm365\MultiCompanies;
use Mm365\DropdownManager;
use Mm365\Posttypes;
use Mm365\Metaoptions;
use Mm365\Themeoptions;
use Mm365\Certification;
use Mm365\Matchrequest;
use Mm365\Matchmaking;
use Mm365\ManageMatchrequests;
use Mm365\AdminDashboard;
use Mm365\Meetings;
use Mm365\CouncilManagers;
use Mm365\CompanySearch;
use Mm365\ManageBuyers;
use Mm365\DataImporter;
use Mm365\Mm365API;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * The initation loader for Matchmaker365 Core, and the main plugin file.

 * Plugin Name:  Matchmaker365 Core
 * Plugin URI:   https://v2soft.com
 * Description:  This plugin handles core logics and all major fuctionalities for Matchmaker365 Portal
 * Author:       V2Soft team
 * Author URI:   https://v2soft.com
 * Version:      3.1.3
 *
 * Text Domain:  matchmaker
 * Domain Path:  languages
 *
 */

/**
 * v3.1.3
 * NEW API Endpoint
 * 
 * v 3.1.2
 * Fixed freezing issue (matchrequest editing)
 * Drop down value change for company size
 * 
 * V 3.1.1
 * Phase to changes
 * 
 * Version 3.1.0
 * Algorith revision, Naics suggester
 * 
 * 
 * Version 3.0.9
 * New match request layout
 * 
 * Version 3.0.8
 * Validate NAICS code. naics code CSV file added
 * 
 * Version 3.0.7
 * Major Update: Match request based on NAICS code instead of keywords
 * Various Bug fixes
 * 
 * Version 3.0.6
 * New validation rule for zip code
 * 
 * Version 3.0.5
 * Theme options reverted
 * Company city display bug fix
 * 
 * Version 3.0.4
 * NAICS code field moved to top for company registration
 * 
 * Version 3.0.3
 * Bug fixes minor
 * 
 * Version 3.0.2
 * Match preference toggle inverted
 * 
 * 
 * Version 3.0.1
 * User switching for Super Buyer
 * Bug fixes
 * Meeting attendee filter for match approval screen
 * 
 * Version 3.0.0
 * Class Auto Loading - Major revamp
 * Traits
 * 
 * Version 2.9
 * Super Buyer module - Login for super buyer
 * Meetings list, super buyer dashboard etc
 * 
 * Version 2.8
 * Conference Module Started - Offlince Conference
 * 
 * Version 2.7
 * New landing page / Removing elementor
 * 
 * Version 2.6
 * Subscription mangement module and various improvements
 * 
 * 
 * Version 2.5
 * Email notification to users to close the match request which is approved for more than 7 days 
 * Notice on company preview page about certificate upload 
 * 
 * Version 2.4
 * New column to identify the requester (buyer or supplier request)
 * Certified badge and indication column in reports
 * Font size increased for labels
 * 
 * 
 * Version 2.3
 * MBEs occurance counter report for match requests
 * 
 * 
 * Vesrion 2.2
 * Search companies for super admin with additional filters
 * 
 * Vesrion 2.1
 * Buyer blocking module
 * Help page manager
 * 
 * Version 2.0
 * Help Blocks using IntroJS
 * Notification enhancements
 * 
 * 
 * Version 1.9
 * Input sanitization improvements
 * helper: getuserDC($user->ID) use in all occurance instaead of leaving blank
 * Sort council name alphabetically
 * One user can register multiple companies (Major)
 * Importer can import buyer and supplier
 * Importer can add companies to existing users based on email
 * Keeping companya and council ids in cookies
 * Coookie concent plugin
 * 
 * Version 1.8
 * New UI for match result set for Admins with resulted company council name
 * and global search
 * Added Requester council info on match request quick info (above results)
 * Minor UI improvements (Wordwrapping to company preview)
 * New UI for match results for end users
 * 
 * Version 1.7
 * System settings, Importer
 * Do not use child pages 
 * Conditionally deque script etc
 * 
 * Version 1.6
 * Multiple Councils
 * 
 * Version 1.5
 * New match request UI
 * 
 * Version 1.4.1
 * Certificate module
 * 
 * Version 1.4
 * Match request closure module
 * 
 * Version 1.3
 * Looking for module
 * 
 * Version 1.2
 * Meeting schedule Module
 * 
 * Version v1.1
 * This version is adding some enhancement to existing features 
 * and adding some new features like meeting scheduling, certificate
 * verification and some report related changes in management account
 * 
 */



/**
 * This plugin employs procedural and OOPs approach. Make sure the function names are unique
 * Post Types
 * Meta Options Defentions (CMB2 based)
 * Form Handlers
 * Email handlers
 * Cron Handlers
 * Report Handlers
 * Helper functions
 *
 */



// Define the main autoloader for classes
spl_autoload_register( 'mm365_psr4_class_autoloader' );
/**
 * @param string $class The fully-qualified class name.
 * @return void
 */
function mm365_psr4_class_autoloader($class) {
    // replace namespace separators with directory separators in the relative 
    // class name, append with .php
    $class_path = str_replace('\\', '/', $class);
    
    $file =  plugin_dir_path( __FILE__ ) . '/src/classes/' . $class_path . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
}



// Define the main autoloader for classes
spl_autoload_register( 'mm365_psr4_traits_autoloader' );
/**
 * @param string $class The fully-qualified class name.
 * @return void
 */
function mm365_psr4_traits_autoloader($trait) {
    // replace namespace separators with directory separators in the relative 
    // class name, append with .php
    $traits_path = str_replace('\\', '/', $trait);
    
    $file =  plugin_dir_path( __FILE__ ) . '/src/traits/' . $traits_path . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
}



/**
 * Init plugin
 * 
 * 
 */

 add_action( 'plugins_loaded', 'Mm365_init' ); // Hook initialization function
function Mm365_init() {
   
  //Global
  global $mm365;

  new Helpers();
  new Companies();
  new Council();
  new UserDashboard();
  new MultiCompanies();
  new DropdownManager();
  new Posttypes;
  new Metaoptions;
  new Themeoptions;
  new Certification;
  new Matchrequest;
  new Matchmaking;
  new ManageMatchrequests;
  new AdminDashboard;
  new Meetings;
  new CouncilManagers;
  new SuperBuyers;
  new CompanySearch;
  new ManageBuyers;
  new HelpPageManagement;
  new DataImporter;
  new Subscription;
  new CompanyReports;
  new MatchrequestsReports;
  new ConsolidatedReport;
  new SuppliersAppearedinSearchReport;
  new MatchrequestClosure;
  new OfflineConferences;
  new ScheduledTasks;
  new PublicFacing;
  new Mm365API;

}

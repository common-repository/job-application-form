<?php

/**
 * Fired during plugin activation
 *
 * @link       http://sgtechcoder.com/
 * @since      1.0.0
 *
 * @package    Sg_Job_Application_Form
 * @subpackage Sg_Job_Application_Form/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sg_Job_Application_Form
 * @subpackage Sg_Job_Application_Form/includes
 * @author     Sahil Gulati <sgwebsol@gmail.com>
 */
global $jal_db_version;
$jal_db_version = '1.0';
class Sg_Job_Application_Form_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

    
	public static function activate() {
        /*
        * creating database
        */
        global $wpdb;
        global $jal_db_version;

        $table_name = $wpdb->prefix . 'job_list';
        $table_name1 = $wpdb->prefix . 'job_designation';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
        jid mediumint(9) NOT NULL AUTO_INCREMENT,
        fname tinytext NOT NULL,
        lname tinytext NOT NULL,
        jemail text NOT NULL,
        phno text NOT NULL,
        jtype text NOT NULL,
        attchid bigint(12), 
        PRIMARY KEY  (jid)
      ) $charset_collate;";

        

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $sql1 = "CREATE TABLE $table_name1 (
        jdid mediumint(9) NOT NULL AUTO_INCREMENT,
        jdname tinytext NOT NULL, 
        PRIMARY KEY  (jdid)
      ) $charset_collate;";
        dbDelta( $sql1 );

        add_option( 'jal_db_version', $jal_db_version );

	}

}

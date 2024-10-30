<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://sgtechcoder.com/
 * @since             1.0.0
 * @package           Sg_Job_Application_Form
 *
 * @wordpress-plugin
 * Plugin Name:       Job Application Form
 * Plugin URI:        http://sgtechcoder.com/wordpress-plugin-development/
 * Description:       This plugin provide functionality to take job apllication enteries from frontend and displays it in backend with proper format. to display the form in frontend use shorcode "[job-shortcode]".
 * Version:           1.0
 * Author:            Sahil Gulati
 * Author URI:        http://sgtechcoder.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sg-job-application-form
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SG_JOB_APPLICATION_FORM_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sg-job-application-form-activator.php
 */
function activate_sg_job_application_form() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sg-job-application-form-activator.php';
	Sg_Job_Application_Form_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sg-job-application-form-deactivator.php
 */
function deactivate_sg_job_application_form() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sg-job-application-form-deactivator.php';
	Sg_Job_Application_Form_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sg_job_application_form' );
register_deactivation_hook( __FILE__, 'deactivate_sg_job_application_form' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sg-job-application-form.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sg_job_application_form() {

	$plugin = new Sg_Job_Application_Form();
	$plugin->run();

}
run_sg_job_application_form();

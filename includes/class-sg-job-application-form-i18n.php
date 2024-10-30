<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://sgtechcoder.com/
 * @since      1.0.0
 *
 * @package    Sg_Job_Application_Form
 * @subpackage Sg_Job_Application_Form/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Sg_Job_Application_Form
 * @subpackage Sg_Job_Application_Form/includes
 * @author     Sahil Gulati <sgwebsol@gmail.com>
 */
class Sg_Job_Application_Form_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'sg-job-application-form',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}

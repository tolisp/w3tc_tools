<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://intelpad.eu
 * @since      1.0.0
 *
 * @package    W3tc_Tools
 * @subpackage W3tc_Tools/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    W3tc_Tools
 * @subpackage W3tc_Tools/includes
 * @author     Tolis Papastolopoulos <tolis@intelpad.eu>
 */
class W3tc_Tools_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'w3tc-tools',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}

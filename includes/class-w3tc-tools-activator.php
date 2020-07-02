<?php

/**
 * Fired during plugin activation
 *
 * @link       https://intelpad.eu
 * @since      1.0.0
 *
 * @package    W3tc_Tools
 * @subpackage W3tc_Tools/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    W3tc_Tools
 * @subpackage W3tc_Tools/includes
 * @author     Tolis Papastolopoulos <tolis@intelpad.eu>
 */
class W3tc_Tools_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		if (!defined('W3TC')) {

			die('You need to install W3TC Cache plugin!');

		}

	}

}

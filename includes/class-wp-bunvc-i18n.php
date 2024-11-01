<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.coffee-break-designs.com/production/wp-bunvc/
 * @since      1.0.0
 *
 * @package    Wp_bunvc
 * @subpackage Wp_bunvc/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wp_bunvc
 * @subpackage Wp_bunvc/includes
 * @author     coffee break designs <wada@coffee-break-designs.com>
 */
class Wp_bunvc_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wp-bunvc',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}

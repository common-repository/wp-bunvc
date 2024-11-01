<?php

/**
 *
 * @link              https://www.coffee-break-designs.com/
 * @since             1.0.0
 * @package           Wp_bunvc
 *
 * @wordpress-plugin
 * Plugin Name:       WP BunVC
 * Plugin URI:        https://www.coffee-break-designs.com/production/wp-bunvc/
 * Description: Plug-in for smooth payment of virtual currency
 * Version:           1.1.6
 * Author:            wadadanet
 * Author URI:        https://www.coffee-break-designs.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       Wp_bunvc
 * Domain Path:       /languages
 */

/*  Copyright 2017 wadadanet (email : wada@coffee-break-designs.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WP_BUNVC_VERSION', '1.1.6' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-bunvc-activator.php
 */
function activate_wp_bunvc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-bunvc-activator.php';
	Wp_bunvc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-bunvc-deactivator.php
 */
function deactivate_wp_bunvc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-bunvc-deactivator.php';
	Wp_bunvc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_bunvc' );
register_deactivation_hook( __FILE__, 'deactivate_wp_bunvc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-bunvc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_bunvc() {

	$plugin = new Wp_bunvc();
	$plugin->run();

}
run_wp_bunvc();

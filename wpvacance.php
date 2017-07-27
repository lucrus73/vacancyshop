<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.virtualbit.it/
 * @since             1.0.0
 * @package           Wpvacance
 *
 * @wordpress-plugin
 * Plugin Name:       wpvacance
 * Plugin URI:        https://www.virtualbit.it/wpvacance
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.6
 * Author:            Lucio Crusca
 * Author URI:        https://www.virtualbit.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpvacance
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Start with an underscore to hide fields from custom fields list
$vb_wpv_custom_fields_prefix = 'vb_wpvac_cf_';

define('VB_WPV_SUNDAY', 1);
define('VB_WPV_MONDAY', 2);
define('VB_WPV_TUESDAY', 4);
define('VB_WPV_WEDNESDAY', 8);
define('VB_WPV_THURSDAY', 16);
define('VB_WPV_FRIDAY', 32);
define('VB_WPV_SATURDAY', 64);

$vb_wpv_weekdays = array(VB_WPV_SUNDAY => __('Sunday', 'wpvacance'),
                         VB_WPV_MONDAY => __('Monday', 'wpvacance'),
                         VB_WPV_TUESDAY => __('Tuesday', 'wpvacance'),
                         VB_WPV_WEDNESDAY => __('Wednesday', 'wpvacance'),
                         VB_WPV_THURSDAY => __('Thursday', 'wpvacance'),
                         VB_WPV_FRIDAY => __('Friday', 'wpvacance'),
                         VB_WPV_SATURDAY => __('Saturday', 'wpvacance')
    );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpvacance-activator.php
 */
function activate_wpvacance() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpvacance-activator.php';
	Wpvacance_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpvacance-deactivator.php
 */
function deactivate_wpvacance() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpvacance-deactivator.php';
	Wpvacance_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpvacance' );
register_deactivation_hook( __FILE__, 'deactivate_wpvacance' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpvacance.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

require_once plugin_dir_path( __FILE__ ) . '/cmb2/init.php';

$wpvacance_plugin;
$wpvacance_prefix = '_wpvac_';

function run_wpvacance() 
{
  global $wpvacance_prefix, $wpvacance_plugin;
	$wpvacance_plugin = new Wpvacance($wpvacance_prefix);
	$wpvacance_plugin->run();

}
run_wpvacance();

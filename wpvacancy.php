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
 * @package           Wpvacancy
 *
 * @wordpress-plugin
 * Plugin Name:       Vacancy Shop
 * Plugin URI:        https://www.virtualbit.it/wpvacancy
 * Description:       Sets the standard for accommodations bookings e-commerce with WP
 * Version:           prealpha-0.1.10
 * Author:            Lucio Crusca
 * Author URI:        https://www.virtualbit.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpvacancy
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC'))
{
  die;
}

global $vb_wpv_custom_fields_prefix;
$vb_wpv_custom_fields_prefix = 'vb_wpvac_cf_';
global $vb_wpv_basedir;
$vb_wpv_basedir = plugin_dir_path(__FILE__);
global $vb_wpv_baseurl;
$vb_wpv_baseurl = plugin_dir_url(__FILE__);

define('VB_WPV_SUNDAY', 1);
define('VB_WPV_MONDAY', 2);
define('VB_WPV_TUESDAY', 4);
define('VB_WPV_WEDNESDAY', 8);
define('VB_WPV_THURSDAY', 16);
define('VB_WPV_FRIDAY', 32);
define('VB_WPV_SATURDAY', 64);

$vb_wpv_weekdays = array(VB_WPV_SUNDAY => __('Sunday', 'wpvacancy'),
    VB_WPV_MONDAY => __('Monday', 'wpvacancy'),
    VB_WPV_TUESDAY => __('Tuesday', 'wpvacancy'),
    VB_WPV_WEDNESDAY => __('Wednesday', 'wpvacancy'),
    VB_WPV_THURSDAY => __('Thursday', 'wpvacancy'),
    VB_WPV_FRIDAY => __('Friday', 'wpvacancy'),
    VB_WPV_SATURDAY => __('Saturday', 'wpvacancy')
);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpvacancy-activator.php
 */
function activate_wpvacancy()
{
  global $vb_wpv_basedir;
  require_once $vb_wpv_basedir . 'includes/class-wpvacancy-activator.php';
  Wpvacancy_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpvacancy-deactivator.php
 */
function deactivate_wpvacancy()
{
  global $vb_wpv_basedir;
  require_once $vb_wpv_basedir . 'includes/class-wpvacancy-deactivator.php';
  Wpvacancy_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wpvacancy');
register_deactivation_hook(__FILE__, 'deactivate_wpvacancy');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require $vb_wpv_basedir . 'includes/class-wpvacancy.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
require_once $vb_wpv_basedir . '/cmb2/init.php';
require_once $vb_wpv_basedir . '/cmb2-attached-posts/cmb2-attached-posts-field.php';

global $wpvacancy_plugin;

function run_wpvacancy()
{
  global $wpvacancy_plugin, $vb_wpv_basedir;
  $wpvacancy_plugin = new Wpvacancy($vb_wpv_basedir);
  $wpvacancy_plugin->run();
}

run_wpvacancy();

<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           DmsPlugin
 *
 * @wordpress-plugin
 * Plugin Name:       Delivery Management System
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Zhixin
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('DMS_VERSION', '1.0.0');
define('DMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DMS_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_dms()
{
	require_once DMS_PLUGIN_PATH . 'includes/class-dms-activator.php';
	$activator = new DMS_Activator();
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_dms()
{
	require_once DMS_PLUGIN_PATH . 'includes/class-dms-deactivator.php';
	$deactivator = new DMS_Deactivator();
	$deactivator->deactivate();
}

register_activation_hook(__FILE__, 'activate_dms');
register_deactivation_hook(__FILE__, 'deactivate_dms');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require DMS_PLUGIN_PATH . 'includes/class-dms.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name()
{

	$plugin = new DMS();
	$plugin->run();
}
run_plugin_name();

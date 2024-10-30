<?php
/**
 * CC Admin Emails
 *
 *
 * @package   CC_Admin_Emails
 * @author    Nathan Marks <nmarks@nvisionsolutions.ca>
 * @license   GPL-2.0+
 * @link      http://www.nvisionsolutions.ca
 * @copyright 2014 Nathan Marks
 *
 * @wordpress-plugin
 * Plugin Name:       CC Admin Emails
 * Plugin URI:        http://www.nvisionsolutions.ca
 * Description:       Add other recipients to admin-bound emails!
 * Version:           1.0.0
 * Author:            Nathan Marks
 * Author URI:        http://www.nvisionsolutions.ca
 * Text Domain:       cc-admin-emails
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/nathanmarks/wordpress-cc-admin-emails
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-cc-admin-emails.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'CC_Admin_Emails', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CC_Admin_Emails', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'CC_Admin_Emails', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-cc-admin-emails-admin.php' );
	add_action( 'plugins_loaded', array( 'CC_Admin_Emails_Admin', 'get_instance' ) );

}

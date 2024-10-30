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
 */

/**
 *
 *
 * @package CC_Admin_Emails
 * @author  Nathan Marks <nmarks@nvisionsolutions.ca>
 */
class CC_Admin_Emails {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'cc-admin-emails';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;


	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Our primary filter that does our magic
		add_filter('wp_mail',array($this,'add_cc_emails'));

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * This is where teh magic happens
	 * 
	 * @since 1.0.0
	 * @param array $args the mail args
	 */
	public function add_cc_emails($args) {
		// Is the plugin enabled?
		if (!get_option('cc_admin_enable'))
			return $args;

		$emails = array();

		// Let's fetch our emails
		if($email_list = get_option('cc_admin_email_list'))
			$emails = explode(',', $email_list);

		// Let's see if we've selected a user role
		$email_role = get_option('cc_admin_role_select');

		if($email_role && !empty($email_role) && $email_role !== 'None')
			$emails = array_merge($emails,$this->get_emails_by_role($email_role));

		// Let's make sure none of the emails are the main TO email
		foreach ($emails as $key => $email) {
			if (strstr($args['to'], $email))
				unset($emails[$key]);
		}

		// Is the header Cc arg set?
		if (isset($args['headers']['Cc'])) {
			$current_cc = preg_replace('/\s+/', '', str_replace("Cc:", "", $args['headers']['Cc']));
			if (!empty($current_cc))
				$emails = array_merge($emails,explode(',', $current_cc));
		}

		$emails = implode(',', array_unique($emails));

		$emails = apply_filters('cc_admin_emails_before_validation',$emails,$args);

		if ($this->validate_emails($emails))
			$args['headers']['Cc'] = "Cc: ".$emails;

		return $args;
	}

	/**
	 * Get email addresses by user role
	 */
	private function get_emails_by_role($role) {
		$emails = array();

		$users = get_users(array('role' => $role, 'fields' => array('user_email')));
		foreach ($users as $user) {
			$emails[] = $user->user_email;
		}

		return $emails;
	}

	/**
	 * Basic Validation
	 */
	public static function basic_validation($input ) {	     
	    return strip_tags( stripslashes( $input ) );
	}

	/**
	 * Email List Validation
	 */
	public static function email_list_validation($input) {
		if (empty($input))
			return $input;

		if ($output = self::validate_emails($input)) {
			return $output;
		}
		else {
			add_settings_error(
		    	'cc_admin_email_list',
		    	'cc-admin-invalid-email',
		    	'One or more invalid email addresses were entered, please check your formatting and ensure that multiple email addresses are seperated by commas.'
		    );
		    return false;
		}
	}


	/**
	 * Validate Email
	 */
	public static function validate_emails($input) {
		$output = array();

		$email_array = explode(',', strip_tags( stripslashes( $input ) ));
		foreach ($email_array as $email) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			    // invalid email address
				return false;
			}
			else {
				$output[] = $email;
			}
		}
		return implode(',', $output);
	}
 
}

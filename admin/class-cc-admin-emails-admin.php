<?php
/**
 * CC Admin Emails
 *
 * @package   CC_Admin_Emails_Admin
 * @author    Nathan Marks <nmarks@nvisionsolutions.ca>
 * @license   GPL-2.0+
 * @link      http://www.nvisionsolutions.ca
 * @copyright 2014 Nathan Marks
 */

/**
 *
 * @package CC_Admin_Emails_Admin
 * @author  Nathan Marks <nmarks@nvisionsolutions.ca>
 */
class CC_Admin_Emails_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = CC_Admin_Emails::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action('admin_init',array($this,'add_settings_fields'));

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

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
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'CC Admin Emails', $this->plugin_slug ),
			__( 'CC Admin Emails', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Adds the settings fields for the options page
	 * 
	 * @since 1.0.0
	 */
	public function add_settings_fields() {

		/**
		 * Add the settings section
		 */
		add_settings_section(
			'cc_admin_emails_settings_section',
			'',
			array($this,'render_settings_description'),
			$this->plugin_slug
		);

		/**
		 * Add the email address input
		 */
		add_settings_field(
			'cc_admin_enable',
			'Enable CC Admin Emails',
			array($this,'render_enable_field'),
			$this->plugin_slug,
			'cc_admin_emails_settings_section'
		);

		/**
		 * Add the email address input
		 */
		add_settings_field(
			'cc_admin_email_list',
			'Email Addresses',
			array($this,'render_email_list_field'),
			$this->plugin_slug,
			'cc_admin_emails_settings_section',
			array('Enter a comma seperated list of email addresses')
		);

		/**
		 * Add the role selector checkboxes
		 */
		add_settings_field(
			'cc_admin_role_select',
			'User Role',
			array($this,'render_role_select_field'),
			$this->plugin_slug,
			'cc_admin_emails_settings_section',
			array('Select a user role to CC admin emails to. Using this feature will CC admin bound emails to all users belonging to this user role, so use with caution.')
		);

		/**
		 * Register our settings
		 */
		register_setting($this->plugin_slug,'cc_admin_enable',array('CC_Admin_Emails','basic_validation'));
		register_setting($this->plugin_slug,'cc_admin_email_list',array('CC_Admin_Emails','email_list_validation'));
		register_setting($this->plugin_slug,'cc_admin_role_select',array('CC_Admin_Emails','basic_validation'));

	}

	/**
	 * Renders the enable/disable checkboxe
	 * 
	 * @since 1.0.0
	 */
	public function render_enable_field(){
	    $html = '<input type="checkbox" id="cc_admin_enable" name="cc_admin_enable" value="1" ' . checked(1, get_option('cc_admin_enable'), false) . '/>'; 

	    echo $html;
	}

	/**
	 * Render the user role select field
	 * 
	 * @since 1.0.0
	 * @param array $args arguments sent via the add_settings_field function
	 */
	public function render_role_select_field($args) {
		global $wp_roles;

		$html = '<input type="text" id="cc_admin_email_list" class="regular-text" name="cc_admin_email_list" value="'.get_option('cc_admin_email_list').'">';

		$html = '<select id="cc_admin_role_select" name="cc_admin_role_select">';

			$html .= '<option>None</option>';

			foreach ($wp_roles->roles as $role => $array) {
				$html .= '<option value="'.$role.'" '.($role == get_option('cc_admin_role_select') ? 'selected' : '').'>'.$array['name'].'</option>';
			}

		$html .= '</select>';

		$html .= '<p class="description">'.$args[0].'</p>';

		echo $html;
	}

	/**
	 * Render the email address list field
	 * 
	 * @since  1.0.0
	 * @param array $args arguments sent via the add_settings_field function
	 */
	public function render_email_list_field($args) {

		$html = '<input type="text" id="cc_admin_email_list" class="regular-text" name="cc_admin_email_list" value="'.get_option('cc_admin_email_list').'">';

		$html .= '<p class="description">'.$args[0].'</p>';

		echo $html;
	}

	/**
	 * Render the settings section description
	 * 
	 * @since 1.0.0
	 */
	public function render_settings_description() {
		echo '<p>By default WordPress only allows for one email address to be set as the admin email.</p><p>Using the options below you can add other recepients to be CCed on all emails sent to the admin email.</p><p>Duplicates will be automatically filtered out should an email already have multiple recipients.</p>';
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}


	

}

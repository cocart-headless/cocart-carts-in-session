<?php
/**
 * CoCart Carts in Session core setup.
 *
 * @author   Sébastien Dumont
 * @category Package
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main CoCart Carts in Session class.
 *
 * @class CoCart_Carts_in_Session
 */
final class CoCart_Carts_in_Session {

	/**
	 * Plugin Version
	 *
	 * @access public
	 * @static
	 */
	public static $version = '1.0.0-alpha.1';

	/**
	 * Required PHP Version
	 *
	 * @access public
	 * @static
	 */
	public static $required_php = '7.0';

	/**
	 * Required CoCart Version
	 *
	 * @access public
	 * @static
	 */
	public static $required_cocart = '2.0.0';

	/**
	 * Initiate CoCart Carts in Session.
	 *
	 * @access public
	 * @static
	 */
	public static function init() {
		self::setup_constants();
		self::includes();

		// Environment checking when activating.
		register_activation_hook( COCART_CIS_FILE, array( __CLASS__, 'activation_check' ) );

		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ), 0 );

		// Plugin activation and deactivation.
		register_activation_hook( COCART_CIS_FILE, array( __CLASS__, 'activated' ) );
		register_deactivation_hook( COCART_CIS_FILE, array( __CLASS__, 'deactivated' ) );
	} // END init()

	/**
	 * Return the name of the package.
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	public static function get_name() {
		return 'CoCart Carts in Session';
	}

	/**
	 * Return the version of the package.
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	public static function get_version() {
		return self::$version;
	}

	/**
	 * Return the path to the package.
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	public static function get_path() {
		return dirname( __DIR__ );
	}
	/**
	 * Setup Constants
	 *
	 * @access public
	 * @static
	 */
	public static function setup_constants() {
		self::define( 'COCART_CIS_ABSPATH', dirname( COCART_CIS_FILE ) . '/' );
		self::define( 'COCART_CIS_PLUGIN_BASENAME', plugin_basename( COCART_CIS_FILE ) );
		self::define( 'COCART_CIS_VERSION', self::$version );
		self::define( 'COCART_CIS_SLUG', 'cocart-carts-in-session' );
		self::define( 'COCART_CIS_URL_PATH', untrailingslashit( plugins_url( '/', COCART_CIS_FILE ) ) );
		self::define( 'COCART_CIS_FILE_PATH', untrailingslashit( plugin_dir_path( COCART_CIS_FILE ) ) );
		self::define( 'COCART_STORE_URL', 'https://cocart.xyz/' );
		self::define( 'COCART_CIS_TRANSLATION_URL', 'https://translate.cocart.xyz/projects/cocart-carts-in-session/' );
	} // END setup_constants()

	/**
	 * Define constant if not already set.
	 *
	 * @access private
	 * @static
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private static function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	} // END define()

	/**
	 * Includes required core files.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function includes() {
		include_once COCART_CIS_FILE_PATH . '/includes/class-cocart-carts-in-session-autoloader.php';
		include_once COCART_CIS_FILE_PATH . '/includes/class-cocart-carts-in-session-helpers.php';
		require_once COCART_CIS_FILE_PATH . '/includes/class-cocart-carts-in-session-install.php';
		include_once COCART_CIS_FILE_PATH . '/includes/admin/class-cocart-carts-in-session-admin.php';
	} // END includes()

	/**
	 * Checks the server environment and other factors and deactivates the plugin if necessary.
	 *
	 * @access public
	 * @static
	 */
	public static function activation_check() {
		if ( ! CoCart_Carts_in_Session_Helpers::is_environment_compatible() ) {
			self::deactivate_plugin();
			wp_die( sprintf( __( '%1$s could not be activated. %2$s', 'cocart-carts-in-session' ), 'CoCart Carts in Session', CoCart_Carts_in_Session_Helpers::get_environment_message() ) );
		}
	} // END activation_check()

	/**
	 * Deactivates the plugin if the environment is not ready.
	 *
	 * @access public
	 * @static
	 */
	public static function deactivate_plugin() {
		deactivate_plugins( plugin_basename( COCART_CIS_FILE ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	} // END deactivate_plugin()

	/**
	 * Load the plugin translations if any ready.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/cocart-carts-in-session/cocart-carts-in-session-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/cocart-carts-in-session-LOCALE.mo
	 *
	 * @access public
	 * @static
	 */
	public static function load_plugin_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'cocart-carts-in-session' );

		unload_textdomain( 'cocart-carts-in-session' );
		load_textdomain( 'cocart-carts-in-session', WP_LANG_DIR . '/cocart-carts-in-session/cocart-carts-in-session-' . $locale . '.mo' );
		load_plugin_textdomain( 'cocart-carts-in-session', false, plugin_basename( dirname( COCART_CIS_FILE ) ) . '/languages' );
	} // END load_plugin_textdomain()

	/**
	 * Runs when the plugin is activated.
	 *
	 * Adds plugin to list of installed CoCart add-ons.
	 *
	 * @access public
	 * @static
	 */
	public static function activated() {
		$addons_installed = get_site_option( 'cocart_addons_installed', array() );

		$plugin = plugin_basename( COCART_CIS_FILE );

		// Check if plugin is already added to list of installed add-ons.
		if ( ! in_array( $plugin, $addons_installed, true ) ) {
			array_push( $addons_installed, $plugin );
			update_site_option( 'cocart_addons_installed', $addons_installed );
		}
	} // END activated()

	/**
	 * Runs when the plugin is deactivated.
	 *
	 * Removes plugin from list of installed CoCart add-ons.
	 *
	 * @access public
	 * @static
	 */
	public static function deactivated() {
		$addons_installed = get_site_option( 'cocart_addons_installed', array() );

		$plugin = plugin_basename( COCART_CIS_FILE );
		
		// Remove plugin from list of installed add-ons.
		if ( in_array( $plugin, $addons_installed, true ) ) {
			$addons_installed = array_diff( $addons_installed, array( $plugin ) );
			update_site_option( 'cocart_addons_installed', $addons_installed );
		}
	} // END deactivated()

} // END class
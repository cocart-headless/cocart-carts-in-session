<?php
/*
 * Plugin Name: CoCart - Carts in Session
 * Plugin URI:  https://cocart.xyz
 * Description: Allows you to view all the carts in session via the WordPress admin.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     1.0.0-alpha.3
 * Text Domain: cocart-carts-in-session
 * Domain Path: /languages/
 *
 * Requires at least: 5.3
 * Requires PHP: 7.0
 * WC requires at least: 4.3
 * WC tested up to: 4.9
 *
 * Copyright: © 2021 Sébastien Dumont, (mailme@sebastiendumont.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COCART_CIS_FILE' ) ) {
	define( 'COCART_CIS_FILE', __FILE__ );
}

// Include the main CoCart Carts in Session class.
if ( ! class_exists( 'CoCart_Carts_in_Session', false ) ) {
	include_once untrailingslashit( plugin_dir_path( COCART_CIS_FILE ) ) . '/includes/class-cocart-carts-in-session.php';
}

/**
 * Returns the main instance of CoCart Carts in Session and only runs if it does not already exists.
 *
 * @return CoCart_Carts_in_Session
 */
if ( ! function_exists( 'CoCart_Carts_in_Session' ) ) {
	function CoCart_Carts_in_Session() {
		return CoCart_Carts_in_Session::init();
	}

	CoCart_Carts_in_Session();
}
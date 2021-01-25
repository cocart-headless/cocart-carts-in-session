<?php
/**
 * CoCart Carts in Session - Admin.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart Carts in Session\Admin
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Carts_in_Session_Admin' ) ) {

	class CoCart_Carts_in_Session_Admin {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'includes' ) );
			add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 *
		 * @access public
		 */
		public function includes() {
			include COCART_CIS_FILE_PATH . '/includes/admin/class-cocart-carts-in-session-admin-list-carts.php'; // List Carts
		} // END includes()

		/**
		 * Include admin files conditionally.
		 *
		 * @access public
		 */
		public function conditional_includes() {
			$screen = get_current_screen();

			if ( ! $screen ) {
				return;
			}

			switch ( $screen->id ) {
				case 'plugins':
					include COCART_CIS_FILE_PATH . '/includes/admin/class-cocart-carts-in-session-admin-action-links.php'; // Action Links
					break;
			}
		} // END conditional_includes()

	} // END class

} // END if class exists

return new CoCart_Carts_in_Session_Admin();

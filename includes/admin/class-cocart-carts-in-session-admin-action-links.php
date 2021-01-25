<?php
/**
 * Adds links to CoCart Carts in Session on the plugins page.
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

if ( ! class_exists( 'CoCart_Carts_in_Session_Admin_Action_Links' ) ) {

	class CoCart_Carts_in_Session_Admin_Action_Links {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta'), 10, 3 );
		} // END __construct()

		/**
		 * Plugin row meta links
		 *
		 * @access public
		 * @param  array  $metadata An array of the plugin's metadata.
		 * @param  string $file     Path to the plugin file.
		 * @param  array  $data     Plugin Information
		 * @return array  $metadata
		 */
		public function plugin_row_meta( $metadata, $file, $data ) {
			if ( $file == plugin_basename( COCART_CIS_FILE ) ) {
				$metadata[ 1 ] = sprintf( __( 'Developed By %s', 'cocart-carts-in-session' ), '<a href="' . $data[ 'AuthorURI' ] . '" aria-label="' . esc_attr__( 'View the developers site', 'cocart-carts-in-session' ) . '">' . $data[ 'Author' ] . '</a>' );

				$row_meta = array(
					'translate' => '<a href="' . esc_url( COCART_CIS_TRANSLATION_URL ) . '" aria-label="' . sprintf( esc_attr__( 'Translate %s', 'cocart-carts-in-session' ), 'CoCart Carts in Session' ) . '" target="_blank">' . esc_attr__( 'Translate', 'cocart-carts-in-session' ) . '</a>',
				);

				$metadata = array_merge( $metadata, $row_meta );
			}

			return $metadata;
		} // END plugin_row_meta()

	} // END class

} // END if class exists

return new CoCart_Carts_in_Session_Admin_Action_Links();

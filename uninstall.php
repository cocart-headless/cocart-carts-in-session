<?php
/**
 * CoCart Carts in Session Uninstall
 *
 * Uninstalling CoCart Carts in Session options.
 *
 * @author  Sébastien Dumont
 * @package CoCart Carts in Session\Uninstaller
 * @license GPL-2.0+
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Delete options.
delete_site_option( 'cocart_carts_in_session_install_date' );
delete_site_option( 'cocart_carts_in_session_version' );
<?php
/**
 * Lists the carts in session.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart Carts in Session\Admin
 * @since    1.0.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'CoCart_Admin_Carts_in_Session_List' ) ) {

	class CoCart_Admin_Carts_in_Session_List extends WP_List_Table {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			parent::__construct( array(
				'singular' => __( 'Cart', 'cocart-carts-in-session' ), // Singular label of the listed carts.
				'plural'   => __( 'Carts', 'cocart-carts-in-session' ), // Plural label, also this well be one of the table css class.
				'ajax'     => false // We won't support Ajax for this table.
			 ) );

			add_action( 'cocart_carts_in_session_table_bottom', array( $this, 'maybe_render_blank_state' ) );
			?>
			<style type="text/css">
			.cocart_page_cocart-carts-in-session .wp-list-table th {
				width: 12ch;
				vertical-align: middle;
			}

			.cocart_page_cocart-carts-in-session .wp-list-table .column-cart_key,
			.cocart_page_cocart-carts-in-session .wp-list-table .column-name,
			.cocart_page_cocart-carts-in-session .wp-list-table .column-email {
				width: 20ch;
			}

			.cocart_page_cocart-carts-in-session .wp-list-table .column-item_count,
			.cocart_page_cocart-carts-in-session .wp-list-table .column-date_created,
			.cocart_page_cocart-carts-in-session .wp-list-table .column-date_expires,
			.cocart_page_cocart-carts-in-session .wp-list-table .column-value {
				width: 8.8ch;
			}

			body.has-woocommerce-navigation.cocart_page_cocart-carts-in-session .wp-list-table .column-item_count,
			body.has-woocommerce-navigation.cocart_page_cocart-carts-in-session .wp-list-table .column-date_created,
			body.has-woocommerce-navigation.cocart_page_cocart-carts-in-session .wp-list-table .column-date_expires,
			body.has-woocommerce-navigation.cocart_page_cocart-carts-in-session .wp-list-table .column-value {
				width: 10ch;
			}

			.cocart_page_cocart-carts-in-session .wp-list-table .column-value {
				width: 8ch;
				text-align: right;
			}

			.cocart_page_cocart-carts-in-session .wp-list-table tbody td,
			.cocart_page_cocart-carts-in-session .wp-list-table tbody th {
				padding: 0.8em;
				line-height: 26px;
			}

			.cart-status {
				display: -webkit-inline-box;
				display: inline-flex;
				line-height: 2.5em;
				color: #777;
				background: #e5e5e5;
				border-radius: 4px;
				border-bottom: 1px solid rgba(0,0,0,.05);
				margin: -.25em 0;
				cursor: inherit !important;
				white-space: nowrap;
				max-width: 100%;
			}

			.cart-status > span {
				margin: 0 1em;
				overflow: hidden;
				text-overflow: ellipsis;
			}

			.cart-status.status-active {
				background: #dcc8e1;
				color: #4b2e53;
			}

			.cart-status.status-expiring {
				background: #f8dda7;
				color: #94660c;
			}

			.cart-status.status-abandoned {
				background: #eba3a3;
				color: #761919;
			}

			.cart-status.status-processing {
				background: #c6e1c6;
				color: #5b841b;
			}
		</style>
		<?php
		}

		/**
		 * Filter the carts listed by status.
		 *
		 * @access protected
		 * @return Array $status_links
		 */
		protected function get_views() {
			$status_links = array();
			$current = ( ! empty( $_GET['cart_status'] ) ? esc_html( $_GET['cart_status'] ) : 'all' );

			$class = ($current == 'all' ? ' class="current"' : '');
			$all_url = remove_query_arg( 'cart_status' );
			$status_links['all'] = sprintf( __( '<a href="%1$s"%2$s>All (%3$s)</a>', 'cocart-carts-in-session' ), $all_url, $class, CoCart_Admin_WC_System_Status::carts_in_session() );

			if ( method_exists( 'CoCart_Admin_WC_System_Status', 'count_carts_active' ) ) {
				$class = ($current == 'active' ? ' class="current"' : '');
				$active_url = add_query_arg( 'cart_status', 'active' );
				$status_links['active'] = sprintf( __( '<a href="%1$s"%2$s>Active (%3$s)</a>', 'cocart-carts-in-session' ), $active_url, $class, CoCart_Admin_WC_System_Status::count_carts_active() );
			}

			$class = ($current == 'abandoned' ? ' class="current"' : '');
			$abandoned_url = add_query_arg( 'cart_status', 'abandoned' );
			$status_links['abandoned'] = sprintf( __( '<a href="%1$s"%2$s>Abandoned (%3$s)</a>', 'cocart-carts-in-session' ), $abandoned_url, $class, CoCart_Admin_WC_System_Status::count_carts_expired() );

			$status_links['expiring'] = sprintf( __( '<a href="%s">Expiring (%s)</a>', 'cocart-carts-in-session' ), '#', CoCart_Admin_WC_System_Status::count_carts_expiring() );

			return $status_links;
		}

		/**
		 * Add extra markup in the toolbars before or after the list.
		 * 
		 * @access public
		 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list.
		 */
		public function extra_tablenav( $which ) {
			do_action( 'cocart_carts_in_session_table_' . $which );
		}

		/**
		 * Displays a blank state should there be no carts in session.
		 *
		 * @access public
		 * @return void
		 */
		public function maybe_render_blank_state() {
			if ( $this->record_count() < 1 ) {
				echo '<div class="woocommerce-BlankState">';

				echo '<h2 class="woocommerce-BlankState-message">' . esc_html__( 'When a customer adds an item to the cart, that cart will appear here.', 'cocart-carts-in-session' ) . '</h2>';

				echo '</div>';

				echo '<style type="text/css">#posts-filter .wp-list-table, #posts-filter .tablenav.top, .tablenav.bottom .actions, .wrap .subsubsub  { display: none; } #posts-filter .tablenav.bottom { height: auto; } </style>';
			}
		}

		/**
		 * Retrieve carts data from the database.
		 *
		 * @access public
		 * @static
		 * @global $wpdb
		 * @param  int $per_page
		 * @param  int $page_number
		 * @return mixed
		 */
		public static function get_carts( $per_page = 20, $page_number = 1 ) {
			global $wpdb;

			$orderby = ! empty( $_GET['orderby'] ) ? wp_unslash( $_GET['orderby'] ) : 'cart_created';
			$order   = ! empty( $_GET['order'] ) ? wp_unslash( $_GET['order'] ) : 'ASC';

			$results = $wpdb->get_results( 
				$wpdb->prepare(
					"
					SELECT *
					FROM {$wpdb->prefix}cocart_carts
					WHERE 1=1
					ORDER BY %s %s
					LIMIT %d
					OFFSET %d",
					$orderby,
					$order,
					$per_page,
					( $page_number - 1 ) * $per_page,
				),
				ARRAY_A
			);

			return $results;
		}

		/**
		 * Returns the count of carts in the database.
		 *
		 * @access public
		 * @static
		 * @return String
		 */
		public static function record_count() {
			return CoCart_Admin_WC_System_Status::carts_in_session();
		}

		/**
		 * Text displayed when no cart data is available.
		 * 
		 * @access public
		 */
		public function no_items() {
			_e( 'No carts in session.', 'cocart-carts-in-session' );
		}

		/**
		 * Render a column when no column specific method exist.
		 *
		 * @access public
		 * @param  array  $item
		 * @param  string $column_name
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'name':
				case 'email':
				case 'status':
				case 'item_count':
				case 'value':
				case 'date_created':
				case 'date_expires':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ); //Show the whole array for troubleshooting purposes
			}
		}

		/**
		 * Method for cart key column.
		 *
		 * @access public
		 * @param  Array $item an array of DB data
		 * @return String
		 */
		public function column_cart_key( $item ) {
			$cart_key = $item['cart_key'];

			$view_cart = add_query_arg( array(
				'page' => 'cocart-carts-in-session',
				'action' => 'view',
				'cart' => $item['cart_key']
			), admin_url( 'admin.php' ) );

			$actions = array(
				'id'   => 'ID: ' . $item['cart_id'],
				'view' => sprintf( '<a href="%s">%s</a>', $view_cart, esc_html__( 'View Cart', 'cocart-carts-in-session' ) )
			);

			return $cart_key . $this->row_actions( $actions );
		}

		/**
		 * Method for name column.
		 *
		 * @access public
		 * @param  array $item an array of DB data
		 * @return String
		 */
		public function column_name( $item ) {
			$cart_value = maybe_unserialize( $item['cart_value'] );
			$customer   = maybe_unserialize( $cart_value['customer'] );

			$first_name = $customer['first_name'];
			$last_name  = $customer['last_name'];

			if ( ! empty( $first_name ) || ! empty( $last_name ) ) {
				return $first_name . ' ' . $last_name;
			} else {
				return '&ndash;';
			}
		}

		/**
		 * Method for email column.
		 *
		 * @access public
		 * @param  array $item an array of DB data
		 * @return String
		 */
		public function column_email( $item ) {
			$cart_value = maybe_unserialize( $item['cart_value'] );
			$customer   = maybe_unserialize( $cart_value['customer'] );
			$email      = ! empty( $customer ) ? $customer['email'] : '';

			if ( $email ) {
				return '<a href="mailto:' . $email . '">' . $email . '</a>';
			} else {
				return '&ndash;';
			}
		}

		/**
		 * Method for phone column.
		 *
		 * @access public
		 * @param  array $item an array of DB data
		 * @return String
		 */
		public function column_phone( $item ) {
			$cart_value = maybe_unserialize( $item['cart_value'] );
			$customer   = maybe_unserialize( $cart_value['customer'] );
			$phone      = ! empty( $customer ) ? $customer['phone'] : '';

			if ( $phone ) {
				return $phone;
			} else {
				return '&ndash;';
			}
		}

		/**
		 * Method for status column.
		 *
		 * @access public
		 * @param  array $item an array of DB data
		 * @return String
		 */
		public function column_status( $item ) {
			$tooltip = "";

			$time       = time();
			$t_time     = get_the_time( 'Y/m/d g:i:s a' );
			$expires    = date('m/d/Y H:i:s', $item['cart_expiry'] );
			$expires    = mysql2date( 'G', $expires, false );
			$time_ahead = ( HOUR_IN_SECONDS * 6 ) + $time;

			$status = "active"; // Default cart status.

			// If the cart is 6 hours away or less from cart expiration then the cart status is "expiring".
			if ( $time_ahead > $expires && $time < $expires ) {
				$status  = "expiring";
				$tooltip = wc_sanitize_tooltip( esc_html__( 'Cart will be expiring soon.', 'cocart-carts-in-session') );
			}

			// If the cart expiration is less than the time it is now then the cart status is "abondoned".
			if ( $time > $expires ) {
				$status     = "abandoned"; // a.k.a "Expired"
				$cart_value = maybe_unserialize( $item['cart_value'] );
				$customer   = maybe_unserialize( $cart_value['customer'] );
				$customer   = ! empty( $customer ) ? $customer : '';
				$tooltip    = wc_sanitize_tooltip( esc_html__( 'Cart was abondoned.', 'cocart-carts-in-session') );
			}

			$order_id = isset( $item['order_awaiting_payment'] ) ? absint( $item['order_awaiting_payment'] ) : '';
			$order    = $order_id ? wc_get_order( $order_id ) : null;

			if ( $order && $order->has_status( array( 'pending', 'failed' ) ) ) {
				$status  = "processing";
				$tooltip = wc_sanitize_tooltip( esc_html__( 'Cart is currently processing.', 'cocart-carts-in-session') );
			}

			if ( $tooltip ) {
				printf( '<mark class="cart-status %s tips" data-tip="%s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $status ) ), wp_kses_post( $tooltip ), esc_html( $this->get_cart_status_name( $status ) ) );
			} else {
				printf( '<mark class="cart-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $status ) ), esc_html( $this->get_cart_status_name( $status ) ) );
			}
		}

		/**
		 * Method for item count column.
		 *
		 * @access public
		 * @param  array $item an array of DB data
		 * @return String
		 */
		public function column_item_count( $item ) {
			$cart_value = maybe_unserialize( $item['cart_value'] );
			$cart       = maybe_unserialize( $cart_value['cart'] );

			$item_count = 0;

			foreach( $cart as $item ) {
				$item_count = $item_count + ( 1 * $item['quantity'] );
			}

			return $item_count;
		}

		/**
		 * Method for cart value column.
		 *
		 * @access public
		 * @param  array $item an array of DB data
		 * @return String
		 */
		public function column_value( $item ) {
			$cart_value = maybe_unserialize( $item['cart_value'] );

			if ( isset( $cart_value['cart_totals'] ) ) {
				$cart_totals = maybe_unserialize( $cart_value['cart_totals'] );
				$cart_value  = $cart_totals['total'];
			}

			return ( ! is_array( $cart_value ) ) ? wc_price( $cart_value ) : wc_price( 0 );
		}

		/**
		 * Method for cart created column.
		 *
		 * @access public
		 * @param  array $item an array of DB data
		 * @return String
		 */
		public function column_date_created( $item ) {
			$timestamp = isset( $item['cart_created'] ) ? $item['cart_created'] : '';

			if ( ! $timestamp ) {
				return '&ndash;';
			}

			$t_time    = get_the_time( 'Y/m/d g:i:s a' );
			$m_time    = date('m/d/Y H:i:s', $item['cart_created'] );
			$time      = mysql2date( 'G', $m_time, false );
			$time_diff = time() - $time;
	
			$h_time = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $m_time );

			if ( $timestamp > strtotime( '-1 day', time() ) && $timestamp <= time() ) {
				$show_date = sprintf(
					/* translators: %s: human-readable time difference */
					_x( '%s ago', '%s = human-readable time difference', 'cocart-carts-in-session' ),
					human_time_diff( $timestamp, time() )
				);
			} else {
				$show_date = wp_date( 'M j, Y', $timestamp );
			}

			return '<time datetime="' . $h_time . '" title="' . $h_time . '">' . esc_html( $show_date ) . '</time>';
		}

		/**
		 * Method for cart expires column.
		 *
		 * @access public
		 * @param  array $item an array of DB data
		 * @return String
		 */
		public function column_date_expires( $item ) {
			$timestamp = isset( $item['cart_expiry'] ) ? $item['cart_expiry'] : '';

			if ( ! $timestamp ) {
				return '&ndash;';
			}

			$t_time    = get_the_time( 'Y/m/d g:i:s a' );
			$m_time    = date('m/d/Y H:i:s', $item['cart_expiry'] );
			$time      = mysql2date( 'G', $m_time, false );
			$time_diff = time() - $time;
	
			$h_time = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $m_time );

			if ( $timestamp > strtotime( '-1 day', time() ) && $timestamp <= time() ) {
				$show_date = sprintf(
					/* translators: %s: human-readable time difference */
					_x( '%s ago', '%s = human-readable time difference', 'cocart-carts-in-session' ),
					human_time_diff( $timestamp, time() )
				);
			} else {
				$show_date = wp_date( 'M j, Y', $timestamp );
			}

			return '<time datetime="' . $h_time . '" title="' . $h_time . '">' . esc_html( $show_date ). '</time>';
		}

		/**
		 *  Define the columns that are going to be used in the table.
		 *
		 * @access public
		 * @return Array
		 */
		public function get_columns() {
			return $columns = apply_filters( 'cocart_carts_in_session_columns', array(
				'cart_key'     => __( 'Cart Key', 'cocart-carts-in-session' ),
				'name'         => __( 'Name Captured', 'cocart-carts-in-session' ),
				'email'        => __( 'Email Captured', 'cocart-carts-in-session' ),
				'phone'        => __( 'Phone Captured', 'cocart-carts-in-session' ),
				'status'       => __( 'Status', 'cocart-carts-in-session' ),
				'item_count'   => __( 'No. Items', 'cocart-carts-in-session' ),
				'date_created' => __( 'Cart Created', 'cocart-carts-in-session' ),
				'date_expires' => __( 'Cart Expires', 'cocart-carts-in-session' ),
				'value'        => __( 'Cart Value', 'cocart-carts-in-session' ),
			) );
		}

		/**
		 * Define which columns are hidden.
		 *
		 * @access public
		 * @return Array $hidden
		 */
		public function get_hidden_columns() {
			return $hidden = apply_filters( 'cocart_carts_in_session_hidden_columns', array() );
		}

		/**
		 * Define which columns are sortable.
		 *
		 * @access public
		 * @return Array $sortable
		 */
		public function get_sortable_columns() {
			return $sortable = apply_filters( 'cocart_carts_in_session_sortable_columns', array(
				'cart_key'     => array( 'cart_key', true ),
				'date_created' => array( 'date_created', true ),
				'date_expires' => array( 'date_expires', true ),
			) );
		}

		/**
		 * Gets a list of cart statuses.
		 *
		 * @access public
		 * @return Array $cart_statuses
		 */
		public function get_cart_statuses() {
			$cart_statuses = array(
				'cocart-abandoned' => _x( 'Abandoned', 'Cart status', 'cocart-carts-in-session' ),
				'cocart-active'    => _x( 'Active', 'Cart status', 'cocart-carts-in-session' ),
				'cocart-expiring'  => _x( 'Expiring', 'Cart status', 'cocart-carts-in-session' ),
			);

			return apply_filters( 'cocart_carts_in_session_statuses', $cart_statuses );
		}

		/**
		 * Get the nice name for an cart status.
		 *
		 * @access public
		 * @param  string $status Status.
		 * @return String
		 */
		public function get_cart_status_name( $status ) {
			$statuses = $this->get_cart_statuses();
			$status   = 'cocart-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
			$status   = isset( $statuses[ 'cocart-' . $status ] ) ? $statuses[ 'cocart-' . $status ] : $status;

			return $status;
		}

		/**
		 * Handles data query and filter, sorting, and pagination.
		 *
		 * @access public
		 * @return void
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$hidden   = $this->get_hidden_columns();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array(
				$columns,
				$hidden,
				$sortable
			);

			$per_page     = $this->get_items_per_page( 'carts_per_page', 20 );
			$current_page = $this->get_pagenum();
			$offset       = ( $current_page - 1 ) * $per_page;

			$data = $this->get_carts( $per_page, $current_page );

			/*
			* The WP_List_Table class does not handle pagination for us, so we need
			* to ensure that the data is trimmed to only the current page. We can use
			* array_slice() to do that.
			*/
			$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

			$total_items = $this->record_count();

			$this->set_pagination_args( array(
				'total_items' => $total_items, // We have to calculate the total number of items.
				'per_page'    => $per_page, // We have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ), // We have to calculate the total number of pages.
			 ) );

			$this->items = $data;
		}

	} // END class

} // END if class exists

if ( ! class_exists( 'CoCart_Admin_Carts_in_Session' ) ) {

	class CoCart_Admin_Carts_in_Session {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			add_filter( 'set-screen-option', array( $this, 'set_screen' ), 11, 3 );
			add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'carts_in_session' ) );
		}

		/**
		 * Sets the number of carts to show on screen per page.
		 *
		 * @param [type] $status
		 * @param [type] $option
		 * @param [type] $value
		 * @return void
		 */
		public static function set_screen( $status, $option, $value ) {
			return $value;
		}

		/**
		 * Adds "Carts in Session" admin menu and page.
		 *
		 * @access public
		 * @return void
		 */
		public function carts_in_session() {
			$parent_page = 'cocart';
			$page_slug   = 'cocart-carts-in-session';

			// If white label is enabled, move under WooCommerce.
			if ( defined( 'COCART_WHITE_LABEL' ) && false !== COCART_WHITE_LABEL ) {
				$parent_page = 'woocommerce';
				$page_slug   = 'carts-in-session';
			}

			$hook = add_submenu_page(
				$parent_page,
				esc_attr__( 'Carts in Session', 'cocart-carts-in-session' ),
				esc_attr__( 'Carts in Session', 'cocart-carts-in-session' ),
				apply_filters( 'cocart_screen_capability', 'manage_options' ),
				$page_slug,
				array( $this, 'list_carts_page' )
			);

			add_action( "load-$hook", array( $this, 'screen_option' ) );

			$page = admin_url( 'admin.php' );

			if ( is_multisite() ) {
				$page = network_admin_url( 'admin.php' );
			}

			// Register WooCommerce Admin Bar.
			if ( CoCart_Carts_in_Session_Helpers::is_wc_version_gte( '4.0' ) && function_exists( 'wc_admin_connect_page' ) ) {
				wc_admin_connect_page(
					array(
						'id'        => 'cocart-carts-in-session',
						'screen_id' => 'cocart_page_cocart-carts-in-session',
						'title'     => array(
							esc_html__( 'CoCart', 'cocart-carts-in-session' ),
							esc_html__( 'Carts in Session', 'cocart-carts-in-session' ),
						),
						'path'      => add_query_arg(
							array(
								'page' => 'cocart-carts-in-session'
							),
							$page
						),
					)
				);
			}

			/**
			 * Adds to the CoCart category for the new WooCommerce Navigation Menu if it exists.
			 * If using a version of CoCart lower than v3 it will display under "Extensions".
			 */
			if ( class_exists( '\Automattic\WooCommerce\Admin\Features\Navigation\Menu' ) ) {
				if ( version_compare( CoCart_Helpers::get_cocart_version(), '3.0.0', '<' ) ) {
					Automattic\WooCommerce\Admin\Features\Navigation\Menu::add_plugin_item(
						array(
							'id'         => 'cocart-carts-in-session-page',
							'title'      => esc_html__( 'Carts in Session', 'cocart-carts-in-session' ),
							'capability' => apply_filters( 'cocart_screen_capability', 'manage_options' ),
							'url'        => 'cocart-carts-in-session',
						)
					);
				} else {
					Automattic\WooCommerce\Admin\Features\Navigation\Menu::add_plugin_item(
						array(
							'id'         => 'cocart-carts-in-session-page',
							'title'      => esc_html__( 'Carts in Session', 'cocart-carts-in-session' ),
							'capability' => apply_filters( 'cocart_screen_capability', 'manage_options' ),
							'url'        => 'cocart-carts-in-session',
							'parent'     => 'cocart-category'
						)
					);
				}
			}
		}

		/**
		 * Lists the carts in session.
		 *
		 * @access public
		 * @return void
		 */
		public function list_carts_page() {
			$carts_in_session = new CoCart_Admin_Carts_in_Session_List();
			$carts_in_session->prepare_items();
			?>
			<div class="wrap">
				<h2 class="wp-heading-inline"><?php echo esc_attr__( 'Carts in Session', 'cocart-carts-in-session' ); ?></h2>

				<?php $carts_in_session->views(); ?>

				<form method="get">
					<?php $carts_in_session->display(); ?>
				</form>
				<div class="clear"></div>
			</div>
		<?php
		}

		/**
		 * Screen options.
		 *
		 * @access public
		 * @return void
		 */
		public function screen_option() {
			$args = array(
				'label'   => __( 'Carts', 'cocart-carts-in-session' ),
				'default' => 20,
				'option'  => 'carts_per_page'
			);

			add_screen_option( 'per_page', $args );
		}

	} // END class

} // END if class exists

return new CoCart_Admin_Carts_in_Session();

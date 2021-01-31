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
		}

		/**
		 * Filter the carts listed by status.
		 *
		 * @access protected
		 * @return Array $status_links
		 */
		protected function get_views() { 
			$status_links = array(
				'all'       => sprintf( __( '<a href="%s">All (%s)</a>', 'cocart-carts-in-session' ), '#', CoCart_Admin_WC_System_Status::carts_in_session() ),
				'abandoned' => sprintf( __( '<a href="%s">Abandoned (%s)</a>', 'cocart-carts-in-session' ), '#', CoCart_Admin_WC_System_Status::count_carts_expired() ),
				'expiring'  => sprintf( __( '<a href="%s">Expiring (%s)</a>', 'cocart-carts-in-session' ), '#', CoCart_Admin_WC_System_Status::count_carts_expiring() )
			);

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

			$sql = "
			SELECT * FROM {$wpdb->prefix}cocart_carts
			";

			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
			}

			$sql .= " LIMIT $per_page";
			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

			$results = $wpdb->get_results( $sql, 'ARRAY_A' );

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
			) );

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

			$email = $customer['email'];

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

			$phone = $customer['phone'];

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

			$status = "active"; // Default status

			if ( $tooltip ) {
				printf( '<mark class="order-status %s tips" data-tip="%s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $status ) ), wp_kses_post( $tooltip ), esc_html( $this->get_cart_status_name( $status ) ) );
			} else {
				printf( '<mark class="order-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $status ) ), esc_html( $this->get_cart_status_name( $status ) ) );
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
			$item_count = count( (array) maybe_unserialize( $cart_value['cart'] ) );

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
			if ( isset( $item['cart_created'] ) && $item['cart_created'] > 0 ) {
				$t_time    = get_the_time( 'Y/m/d g:i:s a' );
				$m_time    = date('m/d/Y H:i:s', $item['cart_created'] );
				$time      = mysql2date( 'G', $m_time, false );
				$time_diff = time() - $time;
		
				$h_time = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $m_time );

				return '<time datetime="' . $h_time . '" title="' . $h_time . '">' . $h_time . '</time>';
			} else {
				return '-';
			}
		}

		/**
		 * Method for cart expires column.
		 *
		 * @access public
		 * @param  array $item an array of DB data
		 * @return String
		 */
		public function column_date_expires( $item ) {
			$t_time    = get_the_time( 'Y/m/d g:i:s a' );
			$m_time    = date('m/d/Y H:i:s', $item['cart_expiry'] );
			$time      = mysql2date( 'G', $m_time, false );
			$time_diff = time() - $time;
	
			$h_time = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $m_time );

			return '<time datetime="' . $h_time . '" title="' . $h_time . '">' . $h_time . '</time>';
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
				'value'        => __( 'Cart Value', 'cocart-carts-in-session' ),
				'date_created' => __( 'Cart Created', 'cocart-carts-in-session' ),
				'date_expires' => __( 'Cart Expires', 'cocart-carts-in-session' )
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
			return $sortable = apply_filters( 'cocart_carts_in_session_sortable_columns', array() );
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

			$total_items = $this->record_count();

			$this->set_pagination_args( [
				'total_items' => $total_items, //WE have to calculate the total number of items
				'per_page'    => $per_page //WE have to determine how many items to show on a page
			] );

			$data = array_slice( $data, ( ($current_page-1 )*$per_page ), $per_page );

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
		 * Undocumented function
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
		 * Undocumented function
		 *
		 * @access public
		 * @return void
		 */
		public function carts_in_session() {
			$hook = add_submenu_page(
				'cocart',
				esc_attr__( 'Carts in Session', 'cocart-carts-in-session' ),
				esc_attr__( 'Carts in Session', 'cocart-carts-in-session' ),
				apply_filters( 'cocart_screen_capability', 'manage_options' ),
				'cocart-carts-in-session',
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

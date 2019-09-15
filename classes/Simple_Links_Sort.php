<?php
/**
 * Simple_Links_Sort
 *
 * Handle all the link sorting requirements
 *
 * @author OnPoint Plugins
 *
 * @since 4/15/2015 - ( 4.0 maybe )
 *
 */
class Simple_Links_Sort {
	const NONCE    = 'simple_links_sort';
	const META_KEY = 'simple_links_by_term_%d';

	private function __construct() {
		$this->hooks();
	}


	private function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'js' ) );

		add_action( 'wp_ajax_simple_links_sort', array( $this, 'ajax_sort' ) );
		add_action( 'wp_ajax_simple_links_get_by_category', array( $this, 'ajax_get_links_by_category' ) );

		add_action( 'admin_menu', array( $this, 'ordering_menu' ) );

	}


	/**
	 * The link Ordering Page
	 *
	 * @since 9/11/12
	 * @since 4.6.1 (allow 1000 links per category instead of 200)
	 *
	 * @return void
	 */
	public function link_ordering_page() {
		$args  = array(
			'post_type'   => Simple_Link::POST_TYPE,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
			// @link https://onpointplugins.com/plugin-support/maximum-number-of-links-in-a-category/
			'numberposts' => 1000, //phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
		);
		$links = get_posts( $args );
		foreach ( $links as &$link ) {
			$cats = '';

			// All Cats Assigned to this.
			$all_assigned_cats = get_the_terms( $link->ID, 'simple_link_category' );
			if ( ! is_array( $all_assigned_cats ) ) {
				$all_assigned_cats = array();
			}

			// Create a sting of cats assigned to this link.
			foreach ( $all_assigned_cats as $cat ) {
				$cats .= ' ' . $cat->term_id;
			}

			$link->cats = $cats;
		}

		require SIMPLE_LINKS_DIR . 'admin-views/link-ordering.php';
	}


	/**
	 * Get Links By Category
	 *
	 * Called via ajax to replenish the links ordering list when a category is selected.
	 * We do it honor a 200 links limit per category as well.
	 *
	 * @return void
	 */
	public function ajax_get_links_by_category() {
		$links = Simple_Links_Categories::get_instance()->get_links_by_category( $_POST['category_id'] );
		require SIMPLE_LINKS_DIR . 'admin-views/draggable-links.php';
		die();
	}


	/**
	 * Create the Link Ordering Menu
	 *
	 * @uses This has built in filters to change the permissions of the link ordering and settings
	 * @uses to change the permissions outside of the dashboard settings setup the filters here
	 *
	 * @return void
	 */
	public function ordering_menu() {
		add_submenu_page(
			'edit.php?post_type=simple_link',
			'simple-link-ordering',
			__( 'Link Ordering', 'simple-links' ),
			$this->get_ordering_cap(),
			'simple-link-ordering',
			array( $this, 'link_ordering_page' )
		);

	}


	/**
	 * Get Ordering Cap
	 *
	 * Get the capability required to order links
	 *
	 * @return string
	 */
	public function get_ordering_cap() {
		if ( get_option( 'sl-hide-ordering', false ) ) {
			$cap_for_ordering = apply_filters( 'simple-link-ordering-cap', 'manage_options' );
		} else {
			$cap_for_ordering = apply_filters( 'simple-link-ordering-cap', 'edit_posts' );
		}
		return $cap_for_ordering;
	}


	/**
	 * Ajax Sort
	 *
	 * Set the sort order for the links.
	 * If we have a category set, we set the order just for that category
	 * via post meta. If no category we simply set the menu order of the posts.
	 *
	 * @return void
	 */
	function ajax_sort() {
		check_ajax_referer( self::NONCE );
		global $wpdb;

		if ( empty( $_POST['category_id'] ) ) {
			foreach ( $_POST['post_id'] as $order => $_post_id ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $order, $_post_id ) );
			}
		} else {
			$term_id = (int) $_POST['category_id'];
			foreach ( $_POST['post_id'] as $order => $_post_id ) {
				update_post_meta( $_post_id, sprintf( self::META_KEY, $term_id ), $order );
			}
		}
		die();

	}


	public function js() {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'underscore' );

		$url = admin_url( 'admin-ajax.php?action=simple_links_sort' );
		$url = add_query_arg( '_wpnonce', wp_create_nonce( self::NONCE ), $url );

		$cat_url = admin_url( 'admin-ajax.php?action=simple_links_get_by_category' );
		$cat_url = add_query_arg( '_wpnonce', wp_create_nonce( self::NONCE ), $cat_url );

		$data = array(
			'sort_url' => $url,
			'get_by_category_url' => $cat_url,
		);

		wp_localize_script( 'simple_links_admin_script', 'simple_links_sort', $data );

	}


	//********** SINGLETON FUNCTIONS **********/

	/**
	 * Instance of this class for use as singleton
	 */
	private static $instance;


	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {
		self::$instance = self::get_instance();
	}


	/**
	 * Get (and instantiate, if necessary) the instance of the
	 * class
	 *
	 * @static
	 * @return self
	 */
	public static function get_instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

<?php

/**
 * Simple Link
 *
 * Custom Post Type handler
 *
 * @class   Simple_Link
 * @package Simple Links
 *
 * @since   2.5.3
 *
 */
class Simple_Link {
	const POST_TYPE = 'simple_link';

	private $post_id;

	/**
	 * @var self
	 */
	static $current;

	public function __construct( $id ) {
		$this->post_id = $id;
		self::$current = $this;
	}


	/**
	 * Register Post Type
	 *
	 * Registers the simple_link post type
	 *
	 * @return void
	 */
	public static function register_post_type() {

		$single = __( 'Link', 'simple-links' );
		$plural = __( 'Links', 'simple-links' );

		$args = array(
			'menu_icon'            => 'dashicons-admin-links',
			'labels'               => array(
				'name'                       => __( 'Simple Links', 'simple-links' ),
				'singular_name'              => $single,
				'search_items'               => sprintf( __( 'Search %s', 'simple-links' ), $plural ),
				'popular_items'              => sprintf( __( 'Popular %s', 'simple-links' ), $plural ),
				'all_items'                  => sprintf( __( 'All %s', 'simple-links' ), $plural ),
				'parent_item'                => sprintf( __( 'Parent %s', 'simple-links' ), $single ),
				'parent_item_colon'          => sprintf( __( 'Parent %s:', 'simple-links' ), $single ),
				'edit_item'                  => sprintf( __( 'Edit %s', 'simple-links' ), $single ),
				'update_item'                => sprintf( __( 'Update %s', 'simple-links' ), $single ),
				'add_new_item'               => sprintf( __( 'Add New %s', 'simple-links' ), $single ),
				'new_item_name'              => sprintf( __( 'New %s Name', 'simple-links' ), $single ),
				'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'simple-links' ), $single ),
				'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'simple-links' ), $plural ),
				'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'simple-links' ), $plural ),
				'view_item'                  => sprintf( __( 'View %s', 'simple-links' ), $single ),
				'add_new'                    => sprintf( __( 'Add New %s', 'simple-links' ), $single ),
				'new_item'                   => sprintf( __( 'New %s', 'simple-links' ), $single ),
				'menu_name'                  => __( 'Simple Links', 'simple-links' ),

				'not_found' => sprintf( __( 'No %s found.', 'simple-links' ), $plural ),
				'not_found_in_trash' => sprintf( __( 'No %s found in Trash.', 'simple-links' ), $plural ),
			),
			'hierarchical'          => false,
			'supports'              => array(
				'thumbnail',
				'title',
				'page-attributes',
				'revisions',
			),
			'publicly_queryable'   => false,
			'public'               => false,
			'show_ui'              => true,
			'show_in_nav_menus'    => false,
			'show_in_rest'         => current_user_can( 'edit_posts' ),
			'has_archive'          => false,
			'rewrite'              => false,
			'exclude_from_search'  => true,
			'register_meta_box_cb' => array(
				Simple_Links_Meta_Boxes::get_instance(),
				'meta_box',
			),
		);

		register_post_type( self::POST_TYPE, apply_filters( 'simple-links-register-post-type', $args ) );

	}

}

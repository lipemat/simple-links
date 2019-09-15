<?php
/**
 * Simple_Links_WP_Links
 *
 * @author OnPoint Plugins
 * @since 4.0.3
 *
 */
class Simple_Links_WP_Links {

	private function __construct(){
		$this->hooks();
	}


	private function hooks(){
		//Ajax request to import links
		add_action( 'wp_ajax_simple_links_import_links', array( $this, 'import_links' ) );

		//Remove the WordPress Links from admin menu
		if( get_option( 'sl-remove-links', false ) ){
			add_filter( 'map_meta_cap', array( $this, 'remove_links' ), 99, 2 );
			add_action( 'widgets_init', array( $this, 'remove_links_widget' ), 1 );
		}
	}

	/**
	 * Remove Links
	 *
	 * Removes all traces of the WordPress Built in Links from the Admin
	 *
	 * @uses added to the map_meta_cap filter by self::__construct()
	 *
	 * @param array  $caps
	 * @param string $cap
	 *
	 * @return string
	 */
	function remove_links( $caps, $cap ){
		if( $cap == 'manage_links' ){
			return array( 'do_not_allow' );
		}

		return $caps;
	}


	/**
	 * Remove Links Widget
	 *
	 * Remove the links widget from the admin
	 *
	 * @return void
	 */
	function remove_links_widget(){
		unregister_widget( 'WP_Widget_Links' );
	}


	/**
	 * Imports the WordPress links into this custom post type
	 *
	 * @since 8/19/12
	 * @uses  called using ajax
	 *
	 * @return array( %old_link_id%, %new_post_id% ) - for testability only
	 */
	function import_links(){
		check_ajax_referer( 'simple_links_import_links' );

		//Add the categories from the links
		$old_link_cats = get_terms( 'link_category', array() );
		if( is_array( $old_link_cats ) ){
			foreach( $old_link_cats as $cat ){
				if( !term_exists( $cat->name, Simple_Links_Categories::TAXONOMY ) ){
					$args[ 'description' ] = $cat->description;
					$args[ 'slug' ]        = $cat->slug;
					wp_insert_term( $cat->name, Simple_Links_Categories::TAXONOMY, $args );
				}
			}
		}

		//for testability
		$matches = array();

		//Import Each link
		foreach( get_bookmarks() as $link ){
			$post = array(
				'post_name'   => $link->link_name,
				'post_status' => 'publish',
				'post_title'  => $link->link_name,
				'post_type'   => 'simple_link'
			);

			//Create the new post
			$id = wp_insert_post( $post );

			$matches[ $link->link_id ] = $id;

			//Update Existing post data
			update_post_meta( $id, 'description', $link->link_description );
			update_post_meta( $id, 'target', $link->link_target );
			update_post_meta( $id, 'web_address', $link->link_url );

			//Put the post in the old categories
			$terms = wp_get_object_terms( $link->link_id, 'link_category' );
			if( is_array( $terms ) ){
				foreach( $terms as $term ){
					if( $term_id = term_exists( $term->name, Simple_Links_Categories::TAXONOMY ) ){
						wp_set_object_terms( $id, (int) $term_id[ 'term_id' ], Simple_Links_Categories::TAXONOMY, true );
					}
				}
			}

		}

		return $matches;

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
	public static function init(){
		self::$instance = self::get_instance();
	}


	/**
	 * Get (and instantiate, if necessary) the instance of the
	 * class
	 *
	 * @static
	 * @return self
	 */
	public static function get_instance(){
		if( !is_a( self::$instance, __CLASS__ ) ){
			self::$instance = new self();
		}

		return self::$instance;
	}
}

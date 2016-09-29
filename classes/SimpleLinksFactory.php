<?php

/**
 * Factory class for generating the links list for the widget and shortcode
 *
 * @author  Mat Lipe <mat@matlipe.com>
 * @since   2.0
 *
 *
 * @uses    May be constructed with $args then $this->output() will output the links list
 *
 * @filters May be overridden using the 'simple_links_factory_class' filter
 */
class SimpleLinksFactory {

	public $links = array(); //the retrieved links
	public $type = false; //if this is a shortcode or widget etc.

	//Default args - used for output
	public $args = array(
		'title'                       => false,
		'show_image'                  => false,
		'show_image_only'             => false,
		'image_size'                  => 'thumbnail',
		'fields'                      => false,
		'description'                 => false,
		'show_description_formatting' => false,
		'separator'                   => '-',
		'id'                          => '',
		'remove_line_break'           => false,
		'include_child_categories'    => false

	);

	//Default Query Args - used by getLinks();
	public $query_args = array(
		'order'       => 'ASC',
		'orderby'     => 'menu_order',
		'category'    => false,
		'numberposts' => '-1'
	);


	/**
	 *
	 * Main Constructor, everything goes through here
	 *
	 * @param        $args = array('title'              => false,
	 *                     'category'           => false,
	 *                     'orderby'           => 'menu_order',
	 *                     'count'             => '-1',
	 *                     'show_image'        => false,
	 *                     'show_image_only'   => false,
	 *                     'image_size'        => 'thumbnail',
	 *                     'order'             => 'ASC',
	 *                     'fields'            => false,
	 *                     'description'       => false,
	 *                     'separator'         =>  '-',
	 *                     'id'                =>  false,
	 *                     'remove_line_break' =>  false
	 * @param array  $args - either from shortcode, widget, or custom
	 * @param string $type - used mostly for css classes
	 */
	function __construct( $args, $type = false ){

		$factory = apply_filters( 'simple_links_factory_class', 'SimpleLinksFactory', $args, $this );

		If( $factory != 'SimpleLinksFactory' ){
			return new $factory( $args, $type );
		}


		$this->type = $type;

		$this->parseArgs( $args );

		$this->getLinks();

	}

	/**
	 * Turns whatever args were sent over into a usable arguments array
	 *
	 * @param array $args
	 *
	 * @return array
	 *
	 * @since 9.21.13
	 */
	protected function parseArgs( $args ){

		$args = apply_filters( 'simple_links_args', $args, $this->type );

		if( isset( $args[ 'count' ] ) ){
			$args[ 'numberposts' ] = $args[ 'count' ];
		}


		//Merge with defaults - done this way to split to two lists
		$this->args       = wp_parse_args( $args, $this->args );
		$this->query_args = shortcode_atts( $this->query_args, $args );


		//Change the Random att to rand for get posts
		if( $this->query_args[ 'orderby' ] == 'random' ){
			$this->query_args[ 'orderby' ] = 'rand';
		} else {
			//For Backwards Compatibility
			if( $this->query_args[ 'orderby' ] == 'name' ){
				$this->query_args[ 'orderby' ] = 'title';
			}
		}

		//Setup the fields
		if( $this->args[ 'fields' ] != false ){
			if( ! is_array( $this->args[ 'fields' ] ) ){
				$this->args[ 'fields' ] = explode( ',', $this->args[ 'fields' ] );
			}
		}


		//Add the categories to the query
		if( $this->query_args[ 'category' ] ){
			if( ! is_array( $this->query_args[ 'category' ] ) ){
				$this->query_args[ 'category' ] = explode( ',', $this->query_args[ 'category' ] );
			}

			foreach( $this->query_args[ 'category' ] as $cat ){
				if( is_numeric( $cat ) ){
					$cat = get_term_by( 'id', $cat, Simple_Links_Categories::TAXONOMY );
				} else {
					if ( !empty( get_term_by( 'name', $cat, Simple_Links_Categories::TAXONOMY ) ) ) {
						$cat = get_term_by( 'name', $cat, Simple_Links_Categories::TAXONOMY );
					} else {
						$cat = get_term_by( 'slug', $cat, Simple_Links_Categories::TAXONOMY );
					}
				}
				if( ! empty( $cat->term_id ) ){
					$all_cats[] = $cat->term_id;
				}
			}


			//the categories were invalid so zero will return nothing
			if( empty( $all_cats ) ){
				$all_cats = 0;
			}

			$this->query_args[ 'tax_query' ][ ] = array(
				'taxonomy'         => 'simple_link_category',
				'fields'           => 'id',
				'terms'            => $all_cats,
				'include_children' => $this->args[ 'include_child_categories' ]
			);

			unset( $this->query_args[ 'category' ] );
		}


		$this->query_args = apply_filters( 'simple_links_parsed_query_args', $this->query_args, $this );

		$this->args[ 'type' ] = $this->type;


		return $this->args = apply_filters( 'simple_links_parsed_args', $this->args, $this );


	}


	/**
	 * Retrieves all link categories
	 *
	 * @param string [ $fields ] = ids,names (defaults to names )
	 *
	 * @return object
	 */
	function get_categories( $fields = 'names' ){

		$args = array(
			'hide_empty' => false,
			'fields'     => $fields
		);

		return get_terms( Simple_Links_Categories::TAXONOMY, $args );
	}


	/**
	 * Retrieve the proper links based on argument set earlier
	 *
	 * @return obj
	 *
	 * @since 1.7.14
	 */
	protected function getLinks(){
		$this->query_args[ 'post_type' ]              = Simple_Link::POST_TYPE;
		$this->query_args[ 'posts_per_page' ]         = $this->query_args[ 'numberposts' ];
		$this->query_args[ 'posts_per_archive_page' ] = $this->query_args[ 'numberposts' ];


		// If we are retrieving a single category and ordering by menu order
		// we need to use our special retrieval method to maintain order by that category
		if( $this->query_args[ 'orderby' ] == 'menu_order' &&
		    !empty( $this->query_args[ 'tax_query' ][0]['terms'] ) &&
			count( $this->query_args[ 'tax_query' ][0]['terms'] ) == 1 ){
					$links = Simple_Links_Categories::get_instance()->get_links_by_category(
						$this->query_args[ 'tax_query' ][0]['terms'][0],
						$this->query_args[ 'numberposts' ],
						$this->args[ 'include_child_categories' ]
					);
		} else {
			$links = get_posts( $this->query_args );

		}

		$links = apply_filters( 'simple_links_object', $links, $this->args, $this );


		//backwards compatible
		$links = apply_filters( 'simple_links_' . $this->args[ 'type' ] . '_links_object', $links, $this->args );
		$links = apply_filters( 'simple_links_' . $this->args[ 'type' ] . '_links_object_' . $this->args[ 'id' ], $links, $this->args );

		return $this->links = $links;

	}

	/**
	 * Magic method to allow for echo against the main class
	 *
	 * @uses echo $links
	 */
	function __toString(){
		return $this->output( false );
	}

	/**
	 * Generated the output bases on retrieved links
	 *
	 *
	 * @uses  may be called normally or by using echo with the class
	 * @uses  SimpleLinksTheLink
	 *
	 * @param bool $echo - defaults to false
	 *
	 * @since 11.16.13
	 *
	 * @return String|void
	 */
	function output( $echo = false ){

		if( empty( $this->links ) ){
			return false;
		}

		$output = '';

		//if there is a title
		if( $this->args[ 'title' ] && $this->type != 'widget' ){
			$output .= sprintf( '<h4 class="simple-links-title">%s</h4>', $this->args[ 'title' ] );

		}

		//Start the list
		$markup = apply_filters( 'simple_links_markup', '<ul class="simple-links-list%s" %s>', $this->args, $this );
		if( empty( $this->args[ 'id' ] ) ){
			$output .= sprintf( $markup, '', '' );
		} else {
			$output .= sprintf( $markup, ' ' . $this->args[ 'id' ], 'id="' . $this->args[ 'id' ] . '"' );
		}

		//Add the links to the list
		foreach( $this->links as $link ){
			$link_class = apply_filters( 'simple_links_link_class', 'SimpleLinksTheLink', $this->type, $this->args, $this );

			$link = new $link_class( $link, $this->args, $this->type );

			$output .= $link->output();
		}

		//end the list
		if( has_filter( 'simple_links_markup' ) ){
			$output = force_balance_tags( $output );
		} else {
			$output .= '</ul>';
		}


		$output .= '<!-- End .simple-links-list -->';


		$output = apply_filters( 'simple_links__output', $output, $this->links, $this->args );

		if( $echo ){
			echo $output;
		} else {
			return $output;
		}

	}

}


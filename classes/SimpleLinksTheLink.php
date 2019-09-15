<?php

/**
 * Simple Links The Link
 *
 * Class for generating and interacting with each individual link
 * Each link should be a new instance of the class
 *
 * @author  OnPoint Plugins <mat@onpointplugins.com>
 *
 * @since   2.0
 *
 * @uses    May be constructed a link object or ID and using a echo will output the formatted link
 *
 * @filter  may be overridden using the 'simple_links_link_class' filter
 *
 * @class   SimpleLinksTheLink
 * @package Simple Links
 *
 */
class SimpleLinksTheLink {

	/**
	 * Link
	 *
	 * This post
	 *
	 * @var WP_Post
	 */
	public $link;

	/**
	 * Meta Data
	 *
	 * @var array
	 */
	public $meta_data = array();

	/**
	 * Additional Fields
	 *
	 * @var array
	 */
	public $additional_fields = array();

	/**
	 * Args
	 *
	 * Arguments used throughout
	 *
	 * @var array
	 */
	public $args = array(
		'type'              => false,
		'show_image'        => false,
		'image_size'        => 'thumbnail',
		'id'                => false,
		'show_image_only'   => false,
		'remove_line_break' => false,
	);


	/**
	 * Constructor
	 *
	 * @param WP_Post|int $link - ID or WP_Post
	 * @param array       $args
	 *
	 */
	public function __construct( $link, $args = array() ) {

		$this->args = wp_parse_args( $args, $this->args );
		$this->args = apply_filters( 'simple_links_the_link_args', $this->args, $this );

		$this->link = get_post( $link );

	}


	/**
	 * Magic method for echoing the object
	 *
	 * @uses self::output()
	 * @uses echo $link
	 */
	public function __toString() {
		return $this->output();

	}


	/**
	 * Output
	 *
	 * A single links output
	 *
	 * @param bool $echo - defaults to false;
	 *
	 * @return string
	 *
	 */
	public function output( $echo = false ) {
		if ( ! class_exists( 'WP_Post' ) ) {
			if ( ! is_object( $this->link ) ) {
				return '';
			}
		} else {
			if ( ! is_a( $this->link, 'WP_Post' ) ) {
				return;
			}
		}


		if ( $this->args['show_image'] ) {
			$image = $this->get_image();
		} else {
			$image = '';
		}

		//do not display empty links
		if ( $this->args['show_image_only'] && empty( $image ) ) {
			return;
		}


		$class = 'simple-links-item';
		if ( $this->args['type'] ) {
			$class .= ' simple-links-' . $this->args['type'] . '-item';
		}


		$markup = apply_filters( 'simple_links_link_markup', '<li class="%s" id="link-%s">', $this->link, $this );

		$output = sprintf( $markup, $class, $this->link->ID );

		//Main link output
		$link_output = sprintf( '<a href="%s" target="%s" title="%s" %s>%s%s</a>',
			esc_attr( $this->get_data( 'web_address' ) ),
			esc_attr( $this->get_data( 'target' ) ),
			esc_attr( strip_tags( $this->get_data( 'description' ) ) ),
			empty( $this->meta_data['link_target_nofollow'][0] ) ? '' : 'rel="nofollow"',
			$image,
			$this->link->post_title
		);

		$_filter_params = array(
			$link_output,
			$this->get_data(),
			$this->link,
			$image,
			$this->args,
			$this,
		);

		$output .= apply_filters_ref_array( 'simple_links_link_output', $_filter_params );

		//The description
		if ( $this->args['description'] ) {
			$description = $this->get_data( 'description' );
			if ( ! empty( $description ) ) {
				if ( $this->args['show_description_formatting'] ) {
					$description = wpautop( $description );
				}
				$output .= sprintf( '%s <span class="link-description">%s</span>', $this->args['separator'], $description );
			}
		}

		//The additional fields
		if ( is_array( $this->args['fields'] ) ) {
			$additional_fields = null;
			foreach ( $this->args['fields'] as $field ) {
				$data = $this->get_additional_field( $field );
				if ( ! empty( $data ) ) {
					$additional_fields .= sprintf( '%s <span class="%s additional-field">%s</span>',
						$this->args['separator'],
						str_replace( ' ', '-', strtolower( $field ) ),
						$data
					);
				}
			}

			$output .= apply_filters( 'simple_links_additional_fields_output', $additional_fields, $this->args['fields'], $this );

		}


		//done this way to allow for filtering
		if ( has_filter( 'simple_links_link_markup' ) ) {
			$output = force_balance_tags( $output );
		} else {
			$output .= '</li>';
		}

		$output = apply_filters( 'simple_links_list_item', $output, $this->link, $this );

		//handle the output
		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}


	/**
	 * Get Image
	 *
	 * Gets the links image formatted based on args
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_image() {
		//Remove the post Title if showing image only
		if ( $this->args['show_image_only'] ) {
			$this->link->post_title = '';
		}

		$image             = get_the_post_thumbnail( $this->link->ID, $this->args['image_size'] );
		$this->link->image = $image;

		if ( ! empty( $image ) && ! $this->args['remove_line_break'] ) {
			$image .= '<br>';  //make the ones with returned image have the links below
		}

		return $image;

	}


	/**
	 * Get the links meta data
	 *
	 * @param string $name - name of meta data key (defaults to all meta data );
	 *
	 * @since 4.4.0
	 *
	 * @return mixed
	 */
	public function get_data( $name = null ) {

		if ( empty( $this->meta_data ) ) {
			$this->meta_data  = get_post_meta( $this->link->ID, '' );
			$this->link->meta = $this->meta_data;

			$this->meta_data = apply_filters( 'simple_links_meta', $this->meta_data, $this->link, $this );

			//backward compatibility
			$this->meta_data = apply_filters( 'simple_links_' . $this->args['type'] . '_link_meta_' . $this->args['id'], $this->meta_data, $this->link, $this->args );
			$this->meta_data = apply_filters( 'simple_links_' . $this->args['type'] . '_link_meta', $this->meta_data, $this->link, $this->args );

		}

		//defaults to all data
		if ( ! $name ) {
			return $this->meta_data;
		}

		if ( isset( $this->meta_data[ $name ][0] ) ) {
			$data = apply_filters( 'simple_links_link_data_' . $name, $this->meta_data[ $name ][0], $this->link );

			return $data;
		}

		return false;
	}


	/**
	 * Get a links additional field's value
	 *
	 * @param string [$name] - defaults to all additional fields
	 *
	 * @since 4.4.0
	 *
	 * @return string|array
	 */
	public function get_additional_field( $name = null ) {
		if ( empty( $this->additional_fields ) ) {
			$this->additional_fields = Simple_Links_Meta_Boxes::get_instance()->get_additional_field_values( $this->link->ID );
		}

		if ( ! $name ) {
			return $this->additional_fields;
		}

		if ( isset( $this->additional_fields[ $name ] ) ) {
			return $this->additional_fields[ $name ];
		}

		return false;
	}


}

<?php

/**
 * Simple_Links_Meta_Boxes
 *
 * @author OnPoint Plugins
 * @since  3.0.1
 *
 */
class Simple_Links_Meta_Boxes {
	const ADDITIONAL_FIELDS = 'link_additional_value';
	const NONCE = 'simple-links/meta-box/nonce';


	const DESCRIPTION = 'description';
	const FIELDS = 'additional_fields';
	const WEB_ADDRESS = 'web_address';
	const TARGET = 'target';


	protected $meta_box_descriptions = array();


	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->set_descriptions();

		add_action( 'save_post_' . Simple_Link::POST_TYPE, array( $this, 'meta_save' ), 10, 2 );

	}


	/**
	 * Get a filtered list of available meta fields
	 * Available meta boxes are also determined by this list
	 *
	 * @return array
	 */
	protected function get_meta_fields() {
		$meta_fields = array(
			__( 'Web Address', 'simple-links' )       => self::WEB_ADDRESS,
			__( 'Description', 'simple-links' )       => self::DESCRIPTION,
			__( 'Link Target', 'simple-links' )       => self::TARGET,
			__( 'Additional Fields', 'simple-links' ) => self::FIELDS,
		);

		return apply_filters( 'simple_links_meta_boxes', $meta_fields );
	}


	/**
	 * Get the additional Field Values for a post
	 *
	 * @since 4.4.0
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_additional_field_values( $post_id ) {
		return get_post_meta( $post_id, self::ADDITIONAL_FIELDS, true );
	}


	/**
	 * Set Descriptions
	 *
	 * Set the meta box descriptions
	 *
	 * @uses $this->meta_box_descriptions
	 *
	 *
	 * @return void
	 */
	private function set_descriptions() {
		$desc = array(
			'web_address'       => __( 'Example', 'simple-links' ) . ': <code>http://wordpress.org/</code> ' . __( 'DO NOT forget the', 'simple-links' ) . ' <code>http:// or https://</code>',
			'description'       => __( 'This will be shown when someone hovers over the link, or optionally below the link', 'simple-links' ) . '.',
			'target'            => __( 'Choose the target frame for your link', 'simple-links' ) . '.',
			'additional_fields' => __( 'Values entered in these fields will be available for shortcodes and widgets', 'simple-links' ) . ' ',
		);

		$this->meta_box_descriptions = apply_filters( 'simple-links-meta-box-descriptions', $desc );

	}


	/**
	 * Save all meta fields
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function meta_save( $post_id, $post ) {
		if ( Simple_Link::POST_TYPE !== $post->post_type || wp_is_post_autosave( $post ) ) {
			return;
		}

		if ( empty( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( $_POST[ self::NONCE ] ), self::NONCE ) ) {
			return;
		}


		$meta_fields = $this->get_meta_fields();

		//Go through the options extra fields
		foreach ( $meta_fields as $field ) {
			if ( self::FIELDS !== $field ) {
				if ( empty( $_POST[ $field ] ) ) {
					$_POST[ $field ] = null;
				}
				if ( self::DESCRIPTION === $field ) {
					update_post_meta( $post_id, $field, wp_kses_post( $_POST[ $field ] ) );

				} else {
					update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
				}
			}
		}

		//for the no follow checkbox
		if ( isset( $_POST['link_target_nofollow'] ) ) {
			update_post_meta( $post_id, 'link_target_nofollow', sanitize_text_field( $_POST['link_target_nofollow'] ) );
		} else {
			update_post_meta( $post_id, 'link_target_nofollow', 0 );
		}

		if ( empty( $_POST[ self::ADDITIONAL_FIELDS ] ) ) {
			$_POST[ self::ADDITIONAL_FIELDS ] = array();
		}
		$additional_fields = array_map( 'sanitize_text_field', $_POST[ self::ADDITIONAL_FIELDS ] );
		update_post_meta( $post_id, self::ADDITIONAL_FIELDS, $additional_fields );

	}


	/**
	 * Register the meta boxes
	 *
	 * @uses  Add or remove meta boxes by adding values to the 'simple_links_meta_boxes' array via the filter here
	 * @uses  Add Change or remove meta box descriptions from the array using the 'simple_links_meta_descriptions'
	 *        filter
	 *        ** Any changes to the meta boxes will automatically save and become available for the output via the
	 *        filters there
	 *        ** You have to use the output filters obj to retrieve a new meta boxes value
	 * @uses  add or rem
	 * @since 8/13/12
	 */
	public function meta_box( $post ) {
		//Apply Filters to Change Descriptions of the Meta Boxes
		$this->meta_box_descriptions = apply_filters( 'simple_links_meta_descriptions', $this->meta_box_descriptions );

		$meta_fields = $this->get_meta_fields();

		//Go through each meta box in the filtered array
		foreach ( $meta_fields as $label => $box ) {
			if ( ( self::FIELDS !== $box ) && ( self::TARGET !== $box ) ) {
				add_meta_box( $box . '_links_meta_box', $label, array(
					$this,
					'link_meta_box_output',
				), $post->type, 'advanced', 'high', array(
					//fixes issue where Gutenberg converts to Array
					//@link https://onpointplugins.com/plugin-support/web-address-description-replaced-by-array
					'__block_editor_compatible_meta_box' => false,
					'box'                                => $box,
				) );
			}
		}

		//The link Target meta box
		if ( in_array( self::TARGET, $meta_fields, true ) ) {
			add_meta_box( 'target_links_meta_box', __( 'Link Target', 'simple-links' ), array(
				$this,
				'target_meta_box_output',
			), $post->type, 'advanced', 'high' );
		}
		if ( in_array( self::FIELDS, $meta_fields, true ) ) {
			add_meta_box( 'additional_fields', __( 'Additional Fields', 'simple-links' ), array(
				$this,
				'additional_fields_meta_box_output',
			), $post->type, 'advanced', 'high' );
		}

	}


	/**
	 * The output of the standard meta boxes and fields
	 *
	 * @param WP_Post $post
	 * @param array   $box the args sent to keep track of what fields is sent over
	 *
	 * @since 12.26.12
	 */
	public function link_meta_box_output( $post, $box ) {
		wp_nonce_field( self::NONCE, self::NONCE );
		$box = $box['args']['box'];

		if ( 'description' !== $box ) {
			printf( '<input type="text" name="%s" value="%s" size="100" class="simple-links-input">', esc_attr( $box ), esc_attr( get_post_meta( $post->ID, $box, true ) ) );
		} else {
			wp_editor( get_post_meta( $post->ID, $box, true ), $box, array( 'media_buttons' => false ) );
		}

		if ( isset( $this->meta_box_descriptions[ $box ] ) ) {
			printf( '<p>%s</p>', wp_kses( $this->meta_box_descriptions[ $box ], array( 'code' => array() ) ) );
		}
	}


	/**
	 * Output of the additional fields meta box
	 *
	 *
	 * @since 1.7.14
	 *
	 *
	 */
	public function additional_fields_meta_box_output( $post ) {
		$values = $this->get_additional_field_values( $post->ID );

		$names = simple_links()->get_additional_fields();

		if ( is_array( $names ) ) {
			foreach ( $names as $key => $value ) {
				if ( empty( $values[ $value ] ) ) {
					$values[ $value ] = null;
				}

				printf( '<p>%s:  <input type="text" name="%s[%s]" value="%s" size="70" class="SL-additonal-input">',
					esc_html( $value ), esc_attr( self::ADDITIONAL_FIELDS ), esc_attr( $value ), esc_attr( $values[ $value ] )
				);
			}
		}

		if ( isset( $this->meta_box_descriptions['additional_fields'] ) ) {
			echo '<p>' . wp_kses( $this->meta_box_descriptions['additional_fields'], array( 'code' => array() ) ) . '</p>';

			//this one has a default link to settins so don't show if can't see settings
			if ( current_user_can( Simple_Links_Settings::get_instance()->get_settings_cap() ) ) {
				?>
				<p>
					<?php esc_html_e( 'You may add additional fields which will be available for all links in the ', 'simple-links' ); ?>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=simple_link&page=simple-link-settings' ) ); ?>">
						<?php esc_html_e( 'settings', 'simple-links' ); ?>
					</a>
				</p>
				<?php
			}
		}

	}


	/**
	 * Target Meta Box Output
	 *
	 * The Link Target Radio Buttons Meta Box
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 *
	 */
	public function target_meta_box_output( $post ) {
		$target = get_post_meta( $post->ID, 'target', true );
		if ( 'auto-draft' === $post->post_status ) {
			$target = apply_filters( 'simple-links-default-target', get_option( 'simple-links-default-target' ) );
		}

		require SIMPLE_LINKS_DIR . 'admin-views/link-target.php';

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

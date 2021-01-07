<?php
/**
 * Methods for the Admin Area of Simple Links
 *
 * @uses   called by simple-links.php
 *
 */
if ( class_exists( 'simple_links_admin' ) ) {
	return;
}

class simple_links_admin {
	const POINTER = 'simple-links-admin-pointer';


	/**
	 * Constructor
	 *
	 */
	private function __construct() {
		$this->hooks();
	}


	private function hooks() {
		//Change the post updating messages
		add_filter( 'post_updated_messages', array( $this, 'post_update_messages' ) );

		//Add the jquery
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 1 );

		//Add Contextual help to the necessary screens
		add_action( 'load-simple_link_page_simple-link-settings', array( $this, 'help' ) );

		//Add the shortcode button the MCE editor
		add_action( 'init', array( $this, 'mce_button' ) );

		//Add the filter to the Links Post list
		add_action( 'restrict_manage_posts', array( $this, 'posts_list_cat_filter' ) );
		add_filter( 'request', array( $this, 'post_list_query_filter' ) );

		//Post List Columns Mod
		add_filter( 'manage_simple_link_posts_columns', array( $this, 'post_list_columns' ) );
		add_filter( 'manage_simple_link_posts_custom_column', array( $this, 'post_list_columns_output' ), 0, 2 );
	}


	/**
	 * Links Updated Messages
	 *
	 * Customizes the Message for Post Editing Like updating and creating.
	 *
	 * @since   4.4.0
	 *
	 * @param array $messages
	 *
	 * @return array
	 */
	public function post_update_messages( $messages ) {
		global $post;

		$messages['simple_link'] =
			apply_filters(
				'simple-links-updated-messages',
				array(
					0  => '',
					1  => __( 'Link updated.', 'simple-links' ),
					2  => __( 'Custom field updated.', 'simple-links' ),
					3  => __( 'Custom field deleted.', 'simple-links' ),
					4  => __( 'Link updated.', 'simple-links' ),
					/* translators: %s: date and time of the revision */
					5  => isset( $_GET['revision'] ) ? sprintf( __( 'Link restored to revision from %s.', 'simple-links' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
					6  => __( 'Link published.', 'simple-links' ),
					7  => __( 'Link saved.', 'simple-links' ),
					8  => __( 'Link submitted.', 'simple-links' ),
					/* Translators: Date from $post->post_date */
					9  => sprintf( __( 'Link scheduled for: <strong>%1$s</strong>.', 'simple-links' ), date_i18n( __( 'M j, Y @ G:i', 'simple-links' ), strtotime( $post->post_date ) ) ),
					10 => __( 'Link draft updated.', 'simple-links' ),

				)
			);

		return $messages;

	}


	/**
	 * Adds the output to the custom post list columns created by post_list_columns()
	 *
	 * @param string $column column name
	 * @param int    $post_id
	 *
	 * @uses  called by __construct()
	 *
	 * @return void
	 */
	public function post_list_columns_output( $column, $post_id ) {
		switch ( $column ) {
			case 'web_address':
				echo esc_url( get_post_meta( $post_id, 'web_address', true ) );
				break;
			case 'category':
				$cats = simple_links()->get_link_categories( $post_id );
				if ( is_array( $cats ) ) {
					echo esc_html( implode( ' , ', $cats ) );
				}
				break;
		}
	}


	/**
	 * Post List Columns
	 *
	 * Adds the web address and the categories the the links list
	 *
	 * @param array $defaults
	 *
	 * @return array
	 *
	 */
	public function post_list_columns( $defaults ) {
		//get checkbox and title
		$output = array_slice( $defaults, 0, 2 );

		$output['web_address'] = __( 'Web Address', 'simple-links' );
		$output['category']    = __( 'Link Categories', 'simple-links' );

		$output = array_merge( $output, array_slice( $defaults, 2 ) );

		return $output;

	}


	/**
	 * Update the query request to match the slug of the link category in the links list
	 *
	 * @param array $request the query request so far
	 *
	 * @return array the full request
	 * @since 8.2.13
	 * @uses  called by __construct
	 */
	public function post_list_query_filter( $request ) {
		global $pagenow;

		if ( ! isset( $request['simple_link_category'] ) ) {
			return $request;
		}

		if ( 'edit.php' === $pagenow && isset( $request['post_type'] ) && Simple_Link::POST_TYPE === $request['post_type'] && is_admin() ) {
			if ( ! empty( $_REQUEST['filter_action'] ) ) {
				$request['simple_link_category'] = get_term( $request['simple_link_category'], 'simple_link_category' )->slug;
			}
		}

		return $request;
	}


	/**
	 * Creates a drop down list of the link categories to filter the links by in the posts list
	 *
	 * @since 8.2.13
	 * @return null
	 * @uses  called by __construct
	 */
	public function posts_list_cat_filter() {
		global $typenow;

		if ( Simple_Link::POST_TYPE === $typenow ) {
			$taxonomy     = 'simple_link_category';
			$taxonomy_obj = get_taxonomy( $taxonomy );

			if ( ! isset( $_GET['simple_link_category'] ) ) {
				$_GET['simple_link_category'] = null;
			}

			wp_dropdown_categories(
				array(
					/* translators: Taxonomy label of Simple Links Category */
					'show_option_all' => sprintf( __( 'Show All %s', 'simple-links' ), $taxonomy_obj->label ),
					'taxonomy'        => 'simple_link_category',
					'name'            => 'simple_link_category',
					'orderby'         => 'name',
					'selected'        => sanitize_text_field( wp_unslash( $_GET['simple_link_category'] ) ),
					'hierarchical'    => true,
					'depth'           => 3,
					'show_count'      => true,
					'hide_empty'      => true,
				)
			);
		}

	}


	/**
	 * Generates all contextual help screens for this plugin
	 */
	public function help() {
		$screen = get_current_screen();

		if ( empty( $screen->id ) || ! 'simple_link_page_simple-link-settings' === $screen->id ) {
			return;
		}

		$screen->add_help_tab(
			[
				'id'      => 'wordpress-links',
				'title'   => 'WordPress Links',
				'content' => '<p>' . __( 'WordPress has deprecated the built in links functionality', 'simple-links' ) . '.<br>
                                        ' . __( 'These settings take care of cleaning up the old WordPress links', 'simple-links' ) . '<br>
                                        ' . __( 'By Checking "Remove WordPress Built in Links", the old Links menu will disappear along with the add new admin bar link', 'simple-links' ) . '. <br>
                                        ' . __( ' Pressing the "Import Links" button will automatically copy the WordPress Links into Simple Links. Keep in mind if you press this button twice it will copy the links twice and you will have duplicates', 'simple-links' ) . '.</p>',

			]
		);

		$screen->add_help_tab(
			[
				'id'      => 'additional_fields',
				'title'   => __( 'Additional Fields', 'simple-links' ),
				'content' => '<p>' . __( 'You have the ability to add an unlimited number of additional fields to the links by click the "Add Another" button', 'simple-links' ) . '. <br>
                                            ' . __( "Once you save your changes, these fields will show up on a each link's editing page", 'simple-links' ) . '. <br>
                                            ' . __( 'You will have the ability to select any of these fields to display using the Simple Links widgets', 'simple-links' ) . '. <br>
                                            ' . __( "Each widget gets it's own list of ALL the additional fields, so you may display different fields in different widget areas", 'simple-links' ) . '. <br>
                                            ' . __( 'These fields will also be available by using the shortcode. For instance, if you wanted to display a field titled "author" and a field titled "notes" you shortcode would look something like this', 'simple-links' ) . '
                                            <br>[simple-links fields="' . __( 'author,notes', 'simple-links' ) . '" ]</p>',
			]
		);

		$screen->add_help_tab(
			[
				'id'      => 'permissions',
				'title'   => __( 'Permissions', 'simple-links' ),
				'content' => '<p><strong>' . __( 'This is where you decide how much access editors will have', 'simple-links' ) . '</strong><br>
                        ' . __( '"Hide Link Ordering from Editors", will prevent editors from using the drag and drop ordering page. They will still be able to change the order on the individual link editing Pages', 'simple-links' ) . '.<br>
                        ' . __( '"Show Simple Link Settings to Editors" will allow editors to access the screen you are on right now without restriction', 'simple-links' ) . '.</p>',
			]
		);

		$screen->set_help_sidebar(
			'<p>' . __( 'These Sections will give your a brief description of what each group of settings does. Feel free to start a thread on the support forums if you would like additional help items covered in this section', 'simple-links' ) . '</p>'
		);
	}


	/**
	 * Adds the button to the editor for the shortcode
	 *
	 * @since   8/19/12
	 * @uses    called by init in __construct()
	 * @package mce
	 * @uses    There are a couple methods that had to be called from outside the
	 */
	public function mce_button() {
		add_filter( 'mce_external_plugins', array( $this, 'button_js' ), 99 );
		add_filter( 'mce_buttons_2', array( $this, 'button' ) );
	}


	/**
	 * Attached the plugins js to the mce button
	 *
	 * @since 8/19/12
	 * @uses  called by mce_button()
	 *
	 * @param  array $plugins
	 *
	 * @return array
	 */
	public function button_js( $plugins ) {
		$plugins['simpleLinks'] = SIMPLE_LINKS_JS_DIR . 'editor_plugin.js';

		return $plugins;

	}


	/**
	 * Adds an MCE button to the editor for the shortcode
	 *
	 * @since 8/19/12
	 * @uses  called by mce_button()
	 *
	 * @param array $buttons
	 *
	 * @return array
	 */
	public function button( $buttons ) {
		array_push( $buttons, '|', 'simpleLinks' ); //Add the button to the array with a separator first

		return $buttons;

	}


	/**
	 * Admin flag to let users know where the links are
	 *
	 * @action admin_print_footer_scripts 10 0
	 *
	 * @return void
	 */
	public function pointer_flag() {
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		// Check whether our pointer has been dismissed
		if ( ! in_array( self::POINTER, $dismissed, true ) ) {
			?>
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready(function ($) {
					$('#menu-posts-simple_link').pointer({
						content: '<h3>Simple Links</h3><p><?php echo esc_js( __( 'Manage your Links here.', 'simple-links' ) ); ?></p><p><?php echo esc_js( __( 'Settings, categories, and ordering may also be found under this menu.', 'simple-links' ) ); ?></p>',
						position: {
							edge: 'left',
							align: 'center'
						},
						close: function () {
							jQuery.post(ajaxurl, {
								pointer: '<?php echo esc_js( self::POINTER ); ?>',
								action: 'dismiss-wp-pointer'
							});
						}
					}).pointer('open');
				});
				//]]>
			</script>
			<?php
		}
		// Check whether our pointer has been dismissed
		if ( ! in_array( 'simple-links-shortcode-flag', $dismissed, true ) ) {

			//This is the content that will be displayed
			$pointer_content  = '<h3>' . __( 'Simple Links Shortcode Form', 'simple-links' ) . '</h3>';
			$pointer_content .= '<p>' . __( 'Use this icon to generate a Simple Links shortcode', 'simple-links' ) . '! </p>';

			?>

			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready(function ($) {
					setTimeout(function () {
						//The element to point to
						$('#content_simpleLinks').pointer({
							content: ' <?php echo esc_js( $pointer_content ); ?>',
							position: {
								edge: 'left',
								align: 'center'
							},
							close: function () {
								$.post(ajaxurl, {
									pointer: 'simple-links-shortcode-flag',
									action: 'dismiss-wp-pointer'
								});
							}
						}).pointer('open');
					}, 2000);
				});
				//]]>
			</script>
			<?php
		}
	}


	/**
	 * Add the jquery to the admin
	 *
	 * @return void
	 */
	public function admin_scripts() {
		wp_enqueue_style(
			apply_filters( 'simple_links_admin_style', 'simple_links_admin_style' ),
			SIMPLE_LINKS_CSS_DIR . 'simple.links.admin.css'
		);

		//Add the sortable script
		wp_enqueue_script( 'jquery-ui-sortable' );

		//For the Pointer Flag
		add_action( 'admin_print_footer_scripts', array( $this, 'pointer_flag' ) );
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

		wp_enqueue_script(
			'simple_links_admin_script',
			SIMPLE_LINKS_JS_DIR . 'simple_links_admin.js',
			array( 'jquery' ),
			SIMPLE_LINKS_VERSION
		);

		$config = $this->js_config();

		wp_localize_script( 'simple_links_admin_script', 'Simple_Links_Config', $config );
		wp_localize_script( 'simple_links_admin_script', 'SL_locale', $config['i18n'] );

	}


	public function js_config() {
		$js_config = [
			'importLinksURL' => esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=simple_links_import_links' ), 'simple_links_import_links' ) ),
			'i18n'           => [
				'hide_ordering'       => __( 'This will prevent editors from using the drag and drop ordering.', 'simple-links' ),
				'show_settings'       => __( 'This will allow editors access to this Settings Page.', 'simple-links' ),
				'remove_links'        => __( 'This will remove all traces of the Default WordPress Links.', 'simple-links' ),
				'import_links'        => __( 'This will import all existing WordPress Links into the Simple Links', 'simple-links' ),
				'default_target'      => __( "This will be the link's target when a new link is created.", 'simple-links' ),
				'shortcode'           => __( 'Create [simple-links] shortcode', 'simple-links' ),
				'shortcode_generator' => __( 'Simple Links shortcode generator', 'simple-links' ),
			],
		];

		return apply_filters( 'simple-links/js-config', $js_config );
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

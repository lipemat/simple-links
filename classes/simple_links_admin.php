<?php
/**
 * Methods for the Admin Area of Simple Links
 *
 *
 * @author Mat Lipe <mat@matlipe.com>
 *
 * @uses   called by simple-links.php
 *
 *
 *
 */
if ( class_exists( 'simple_links_admin' ) ) {
	return;
}

class simple_links_admin {

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
		add_action( 'load-post.php', array( $this, 'help' ) );
		add_action( 'load-widgets.php', array( $this, 'help' ) );

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
			apply_filters( 'simple-links-updated-messages', array(
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

			) );

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

			wp_dropdown_categories( array(
				/* translators: Taxonomy label of Simple Links Category */
				'show_option_all' => sprintf( __( 'Show All %s', 'simple-links' ), $taxonomy_obj->label ),
				'taxonomy'        => 'simple_link_category',
				'name'            => 'simple_link_category',
				'orderby'         => 'name',
				'selected'        => sanitize_text_field( $_GET['simple_link_category'] ),
				'hierarchical'    => true,
				'depth'           => 3,
				'show_count'      => true,
				'hide_empty'      => true,
			) );
		}

	}


	/**
	 * Help
	 *
	 * Generates all contextual help screens for this plugin
	 *
	 *
	 * @uses Called at load by __construct
	 *
	 */
	public function help() {
		$shortcode_help = array(
			'id'      => 'simple-links-shortcode',
			'title'   => 'Simple Links Shortcode',
			'content' => '<h5>' . __( 'You Can add a Simple Links List  to content using the shortcode', 'simple-links' ) . '[simple-links]</h5>
                        <p>
                            <em>' . __( 'Look for the puzzle button on the content editors for a form that generates the shortcode for you', 'simple-links' ) . '</em>
						</p>
						<p>
							' . __( 'You may use a few or many of the options as you would like. To use all defaults just enter [simple-links]', 'simple-links' ) . '
						</p>
                        <strong>' . __( 'Supported Options', 'simple-links' ) . ':</strong><br>
                        category   = "' . __( 'Comma separated list of Link Category Names or Ids - defaults to all', 'simple-links' ) . '"<br>
                        include_child_categories = "' . __( "true of false - to include links from your selected categories' child categories as well as links from your selected categories - defaults to false", 'simple-links' ) . '"<br>
                        orderby    = "' . __( 'title, random, or date - defaults to link order', 'simple-links' ) . '"<br>
                        order      = "' . __( 'DESC or ASC - defaults to ASC', 'simple-links' ) . '"<br>
                        count      = "' . __( 'Number of links to show', 'simple-links' ) . '"<br>
                        show_image = "' . __( "true or false - to show the link's image or not", 'simple-links' ) . '"<br>
                        show_image_only = "' . __( "true or false - to show the link's image without the title under it. If show image is not true this does nothing", 'simple-links' ) . '"<br>
                        image_size = "' . __( 'Any size built into WordPress or your theme" - default to thumbnail', 'simple-links' ) . '"<br>
                        remove_line_break =  ' . __( "true or false - Remove Line Break Between Images and Links - default to false", 'simple-links' ) . ' <br>
                        fields     = "' . __( "Comma separated list of the Link's Additional Fields to show", 'simple-links' ) . '"<br>
                        description = "' . __( 'true or false" - to show the description - defaults to false', 'simple-links' ) . '"<br>
                        show_description_formatting = "' . __( 'true or false - to display paragraphs format to match the editor content - defaults to false', 'simple-links' ) . '"<br>
                        separator   = "' . __( 'Any characters to display between fields and description - defaults to ', 'simple-links' ) . '\'-\'"<br>
                        id          = "' . __( 'An optional id for the list', 'simple-links' ) . '"
                        <p>
                             e.g. [simple-links show_image="true" image_size="medium" count="12"]
                        </p>',

		);

		//help for the widgets
		$widget_help = array(
			'id'      => 'simple-links-widget',
			'title'   => 'Simple Links Widget',
			'content' => '<h5>' . __( 'You May Add as Many Simple Links Widgets as You Would Like to Your Widget Areas', 'simple-links' ) . '</h5>
                                    <strong>' . __( 'Widget Options', 'simple-links' ) . ':</strong><br>
                                ' . __( 'Categories', 'simple-links' ) . ' = "' . __( 'Select with link categories to pull from', 'simple-links' ) . '"<br>
3                               ' . __( 'Include Child Categories Of Selected Categories', 'simple-links' ) . ' = "' . __( "If checked, links from your selected categories' child categories will display as well as links from your selected categories", 'simple-links') . '"<br>
                                ' . __( 'Order Links By', 'simple-links' ) . '   = "' . __( 'The Order in Which the Links will Display - defaults to link order', 'simple-links' ) . '"<br>
                                ' . __( 'Order', 'simple-links' ) . ' = "' . __( 'The Order in which the links will Display', 'simple-links' ) . '"<br>
                                ' . __( 'Show Description', 'simple-links' ) . ' = "' . __( "Display the Link's Description", 'simple-links' ) . '<br>
                                ' . __( 'Show Description Formatting', 'simple-links' ) . ' = "' . __( 'Display paragraphs to match the editor content', 'simple-links' ) . '"<br>
                                ' . __( 'Number of LInks', 'simple-links' ) . '  = "' . __( 'Number of links to show', 'simple-links' ) . '"<br>
                                ' . __( 'Show Image', 'simple-links' ) . ' = "' . __( "Check the box to display the Link's Image", 'simple-links' ) . '"<br>
                                ' . __( 'Display Image Without Title', 'simple-links' ) . '       = "' . __( "Check this box display the Link's Image without the Link's title under it. If Show Image is not checked, this will do nothing", 'simple-links' ) . '"<br>
                                ' . __( 'Image Size', 'simple-links' ) . ' = "' . __( 'The Size of Image to Show if the previous box is checked', 'simple-links' ) . '"<br>
                                ' . __( 'Include Additional Fields', 'simple-links' ) . ' = "' . __( "Display values from the Link's Additional Fields", 'simple-links' ) . '"<br>
                                ' . __( 'Field Separator', 'simple-links' ) . '  = "' . __( 'Characters to display between fields and description - defaults to ', 'simple-links' ) . '\'-\'"<br>',
		);

		//The screen we are on
		$screen = get_current_screen();

		if ( empty( $screen->id ) ) {
			return;
		}

		//Each page will have different help content
		switch ( $screen->id ) {

			case 'widgets':
				$screen->add_help_tab( $widget_help );
				break;

			//Normal Pages and posts and widgets - The shortcode help
			case 'page':
			case 'post':
				$screen->add_help_tab( $shortcode_help );
				break;

			//The settings page
			case 'simple_link_page_simple-link-settings':
				$screen->add_help_tab( array(
					'id'      => 'wordpress-links',
					'title'   => 'WordPress Links',
					'content' => '<p>' . __( 'WordPress has deprecated the built in links functionality', 'simple-links' ) . '.<br>
                                        ' . __( 'These settings take care of cleaning up the old WordPress links', 'simple-links' ) . '<br>
                                        ' . __( 'By Checking "Remove WordPress Built in Links", the old Links menu will disappear along with the add new admin bar link', 'simple-links' ) . '. <br>
                                        ' . __( ' Pressing the "Import Links" button will automatically copy the WordPress Links into Simple Links. Keep in mind if you press this button twice it will copy the links twice and you will have duplicates', 'simple-links' ) . '.</p>',

				) );

				$screen->add_help_tab( array(
					'id'      => 'additional_fields',
					'title'   => __( 'Additional Fields', 'simple-links' ),
					'content' => '<p>' . __( 'You have the ability to add an unlimited number of additional fields to the links by click the "Add Another" button', 'simple-links' ) . '. <br>
                                            ' . __( "Once you save your changes, these fields will show up on a each link's editing page", 'simple-links' ) . '. <br>
                                            ' . __( 'You will have the ability to select any of these fields to display using the Simple Links widgets', 'simple-links' ) . '. <br>
                                            ' . __( "Each widget gets it's own list of ALL the additional fields, so you may display different fields in different widget areas", 'simple-links' ) . '. <br>
                                            ' . __( 'These fields will also be available by using the shortcode. For instance, if you wanted to display a field titled "author" and a field titled "notes" you shortcode would look something like this', 'simple-links' ) . '
                                            <br>[simple-links fields="' . __( 'author,notes', 'simple-links' ) . '" ]</p>',
				) );

				$screen->add_help_tab( array(
					'id'      => 'permissions',
					'title'   => __( 'Permissions', 'simple-links' ),
					'content' => '<p><strong>' . __( 'This is where you decide how much access editors will have', 'simple-links' ) . '</strong><br>
                        ' . __( '"Hide Link Ordering from Editors", will prevent editors from using the drag and drop ordering page. They will still be able to change the order on the individual link editing Pages', 'simple-links' ) . '.<br>
                        ' . __( '"Show Simple Link Settings to Editors" will allow editors to access the screen you are on right now without restriction', 'simple-links' ) . '.</p>',
				) );

				$screen->add_help_tab( $shortcode_help );

				$screen->add_help_tab( array(
					'id'      => 'crockpot-recipe',
					'title'   => 'Crock-Pot Recipe',
					'content' => '<p>For folks out the like me that rarely have time to leave the computer and cook, a Crock-Pot meal is a great way
                        to have food hot and ready to eat.
                        </p>
                        <p><strong>Here is one of my favorites recipes "Carne Rellenos"</strong><br>
                        1 can (4 ounces) whole green chilies, drained<br>
                        4 ounces cream cheese, softened<br>
                        1 flank steak (about 2 pounds)<br>
                        1.5 cups salsa verde<br>
                        Slit whole chiles open on one side with sharp knife; stuff with cream cheese. Open steak flat on sheet of waxed paper; score
                        steak and turn over. Lay stuffed chiles across unscored side of steak. Roll up and tie with kitchen string. Place steak in Crock Pot
                        ;pour in salsa. Cover; cook on LOW 6 to 8 hours or on HIGH 3 to 4 hours or until done. Remove stead and cut into 6 pieces. Serve
                        with sauce.</p>',
				) );


				$screen->set_help_sidebar(
					'<p>' . __( 'These Sections will give your a brief description of what each group of settings does. Feel free to start a thread on the support forums if you would like additional help items covered in this section', 'simple-links' ) . '</p>' );

				break;
		}

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
	 * Creates an Admin Flag to let new uses know where the menu is
	 *
	 * @since 2.10.14
	 *
	 * @uses  called in the admin_scripts function
	 *
	 * @return void
	 */
	public function pointer_flag() {

		// Get the list of dismissed pointers for the user
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		// Check whether our pointer has been dismissed
		if ( ! in_array( 'simple-links-flag', $dismissed ) ) {

			//This is the content that will be displayed
			$pointer_content = '<h3>Simple Links</h3>';
			$pointer_content .= '<p>' . __( 'Manage your Links Here. Enjoy', 'simple-links' ) . '! </p>';


			?>
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready(function ($) {

					//The element to point to
					$('#menu-posts-simple_link').pointer({
						content: ' <?php echo $pointer_content; ?>',
						position: {
							edge: 'left',
							align: 'center'
						},
						close: function () {
							jQuery.post(ajaxurl, {
								pointer: 'simple-links-flag',
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
			$pointer_content = '<h3>' . __( 'Simple Links Shortcode Form', 'simple-links' ) . '</h3>';
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
		$js_config = array(
			'importLinksURL' => esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=simple_links_import_links' ), 'simple_links_import_links' ) ),
			'i18n'           => array(
				'hide_ordering'       => __( 'This will prevent editors from using the drag and drop ordering.', 'simple-links' ),
				'show_settings'       => __( 'This will allow editors access to this Settings Page.', 'simple-links' ),
				'remove_links'        => __( 'This will remove all traces of the Default WordPress Links.', 'simple-links' ),
				'import_links'        => __( 'This will import all existing WordPress Links into the Simple Links', 'simple-links' ),
				'default_target'      => __( "This will the the link's target when a new link is created.", 'simple-links' ),
				'add_links'           => __( 'Add Simple Links', 'simple-links' ),
				'shortcode'           => __( 'Simple Links Shortcode', 'simple-links' ),
				'shortcode_generator' => __( 'Simple Links Shortcode Generator', 'simple-links' ),
			),
		);

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

<?php

/**
 * Simple Links Settings
 *
 * An evolution of the settings handling to a single class instead of within the
 * large admin class
 *
 * @class   Simple_Links_Settings
 * @package Simple Links
 *
 *
 */
class Simple_Links_Settings {

	const SLUG = 'simple-link-settings';

	const SCREEN = 'sl-settings-boxes';


	/**
	 * Construct
	 *
	 * Runs when get_instance is called the first time
	 */
	protected function __construct() {

		add_action( 'admin_menu', array(
			$this,
			'register_settings_page',
		), 10, 0 );

		add_action( 'admin_menu', array( $this, 'meta_boxes' ) );

	}


	/**
	 * Register Setting Page
	 *
	 * Creates the submenu and registers the option
	 *
	 * @return void
	 */
	public function register_settings_page() {

		//The Settings Page
		add_submenu_page(
			'edit.php?post_type=simple_link',
			'simple-link-settings',
			__( 'Settings', 'simple-links' ),
			$this->get_settings_cap(),
			self::SLUG,
			array( $this, 'display_settings_page' )
		);

		register_setting( self::SLUG, 'sl-hide-ordering' );
		register_setting( self::SLUG, 'sl-show-settings' );
		register_setting( self::SLUG, 'link_additional_fields', 'array_filter' );
		register_setting( self::SLUG, 'sl-remove-links' );
		register_setting( self::SLUG, 'simple-links-default-target' );

	}


	/**
	 * Get Settings Cap
	 *
	 * Get the required capability to manage settings
	 *
	 * @return string
	 *
	 */
	public function get_settings_cap() {
		if ( ! get_option( 'sl-show-settings', false ) ) {
			$cap_for_settings = apply_filters( 'simple-link-settings-cap', 'manage_options' );
		} else {
			$cap_for_settings = apply_filters( 'simple-link-settings-cap', 'edit_posts' );
		}

		return $cap_for_settings;
	}


	/**
	 * Meta Boxes
	 *
	 * Creates the custom meta boxes
	 *
	 * @since   3.2.14
	 *
	 * @package Settings Page
	 *
	 */
	function meta_boxes() {
		add_meta_box(
			'sl-additional-fields',
			__( 'Additional Fields', 'simple-links' ),
			array( $this, 'additional_fields' ),
			self::SCREEN,
			'advanced',
			'core'
		);

		add_meta_box(
			'sl-wordpress-links',
			__( 'WordPress Links', 'simple-links' ),
			array( $this, 'wordpress_links' ),
			self::SCREEN,
			'advanced',
			'core'
		);

		add_meta_box(
			'sl-permissions',
			__( 'Permissions', 'simple-links' ),
			array( $this, 'permissions' ),
			self::SCREEN,
			'advanced',
			'core'
		);

		add_meta_box(
			'sl-defaults',
			__( 'Defaults', 'simple-links' ),
			array( $this, 'defaults' ),
			self::SCREEN,
			'advanced',
			'core'
		);

	}


	public function wordpress_links() {
		require SIMPLE_LINKS_DIR . 'admin-views/settings-wordpress-links.php';
	}


	/**
	 * defaults
	 *
	 * The output of the defaults settings box
	 *
	 * @return void
	 */
	public function defaults() {
		$target = get_option( 'simple-links-default-target' );
		?>
		<h4>
			<?php esc_html_e( 'These settings will effect this plugins defaults', 'simple-links' ); ?>
		</h4>
		<ul>
			<li>
				<?php esc_html_e( 'Default Link Target', 'simple-links' ); ?>
				: <?php simple_links_questions( 'simple-links-default-target' ); ?>
				<p>
					<label for="link_target_blank" class="selectit">
						<input type="radio" name="simple-links-default-target"
							   value="_blank" <?php checked( $target, '_blank' ); ?>>
						<code>
							_blank</code> &minus; <?php esc_html_e( 'new window or tab', 'simple-links' ); ?>.
					</label>
				</p>
				<p>
					<label for="link_target_top" class="selectit">
						<input type="radio" name="simple-links-default-target"
							   value="_top" <?php checked( $target, '_top' ); ?>>
						<code>
							_top</code>
						&minus; <?php esc_html_e( 'current window or tab, with no frames', 'simple-links' ); ?>.
					</label>
				</p>
				<p>
					<label for="link_target_none" class="selectit">
						<input type="radio" name="simple-links-default-target"
							   value="" <?php checked( $target, '' ); ?>>
						<code>
							_none</code> &minus; <?php esc_html_e( 'same window or tab', 'simple-links' ); ?>.
					</label>
				</p>
			</li>
		</ul>
		<?php
	}


	/**
	 * Permissions
	 *
	 * The output of the Permissions box in the settings page
	 *
	 * @return void
	 */
	public function permissions() {
		?>
		<h4><?php esc_html_e( 'These settings will effect access to this plugins features', 'simple-links' ); ?></h4>
		<ul>
			<li><?php esc_html_e( 'Hide Link Ordering from editors', 'simple-links' ); ?>:
				<input type="checkbox" name="sl-hide-ordering" <?php checked( get_option( 'sl-hide-ordering' ) ); ?>
					   value="1"/>
				<?php simple_links_questions( 'SL-hide-ordering' ); ?>
			</li>
			<li><?php esc_html_e( 'Show Simple Link Settings to editors', 'simple-links' ); ?>:
				<input type="checkbox" name="sl-show-settings"
					<?php checked( get_option( 'sl-show-settings' ) ); ?> value="1"/>
				<?php simple_links_questions( 'SL-show-settings' ); ?>
			</li>

		</ul>

		<?php
	}


	/**
	 * Additional Fields
	 *
	 * The Additional_fields Meta box
	 *
	 * @uses called by the add_meta_box function
	 */
	public function additional_fields() {
		?>
		<h4>
			<?php esc_html_e( "These fields will be available on all link's edit screen, widgets, and shortcodes.", 'simple-links' ); ?>
		</h4>

		<?php
		if ( is_array( simple_links()->get_additional_fields() ) ) {
			foreach ( simple_links()->get_additional_fields() as $field ) {
				?>
				<p>
					<?php esc_html_e( 'Field Name', 'simple-links' ); ?>:
					<input type="text" name="link_additional_fields[]"
						   value="<?php echo esc_attr( trim( $field ) ); ?>"/>
					<span class="link_delete_additional"> X </span>
				</p>
				<?php
			}
		}

		?>
		<p>
			<?php esc_html_e( 'Field Name', 'simple-links' ); ?>:
			<input type="text" name="link_additional_fields[] value="
			"> <span class="link_delete_additional"> X </span>
		</p>

		<!-- Placeholder for JQuery -->
		<span id="link-extra-field" style="display:none">
			<p>
				<?php esc_html_e( 'Field Name', 'simple-links' ); ?>:
				<input type="text" name="link_additional_fields[] value="
				"> <span class="link_delete_additional"> X </span>
			</p>
		</span>

		<span id="link-additional-placeholder"></span>

		<?php
		submit_button( __( 'Add Another', 'simple-links' ), 'secondary', 'simple-link-additional' );

	}


	/**
	 * Display Settings Page
	 *
	 * Outputs the settings page
	 *
	 *
	 * @return void
	 */
	public function display_settings_page() {

		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'simple_links_settings_script', SIMPLE_LINKS_JS_DIR . 'simple_links_settings.js', array( 'jquery' ), '1.0.0' );

		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Simple Links Settings', 'simple-links' ); ?></h2>
			<em><?php esc_html_e( 'Be sure to see the help menu for descriptions', 'simple-links' ); ?></em>

			<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
				<?php
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				settings_fields( self::SLUG );
				do_settings_sections( self::SLUG );
				?>
				<div id="poststuff" class="metabox-holder  has-right-sidebar">
					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">
							<?php do_meta_boxes( 'sl-settings-boxes', 'advanced', null ); ?>
						</div>
					</div>
					<br class="clear"/>
				</div>
				<!-- end poststuff -->
				<?php
				submit_button();
				?>
			</form>
		</div>
		<?php

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

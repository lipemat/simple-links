<?php
/**
 * Plugin Name: Simple Links
 * Plugin URI: https://wordpress.org/plugins/simple-links/
 * Description: Links Manager with many features. Makes managing links simple and robust.
 * Version: 4.7.4
 * Author: Mat Lipe
 * Author URI: https://profiles.wordpress.org/mat-lipe/
 * Text Domain: simple-links
 *
 * @package simple-links
 */

if ( defined( 'SIMPLE_LINKS_VERSION' ) ) {
	return;
}
define( 'SIMPLE_LINKS_VERSION', '4.7.4' );

define( 'SIMPLE_LINKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIMPLE_LINKS_URL', plugin_dir_url( __FILE__ ) );
define( 'SIMPLE_LINKS_ASSETS_URL', SIMPLE_LINKS_URL . 'assets/' );
define( 'SIMPLE_LINKS_IMG_DIR', SIMPLE_LINKS_ASSETS_URL . 'img/' );
define( 'SIMPLE_LINKS_JS_DIR', SIMPLE_LINKS_ASSETS_URL . 'js/' );
define( 'SIMPLE_LINKS_JS_PATH', SIMPLE_LINKS_DIR . 'assets/js/' );
define( 'SIMPLE_LINKS_CSS_DIR', SIMPLE_LINKS_ASSETS_URL . 'css/' );

require __DIR__ . '/template-tags.php';

/**
 * Autoload classes from PSR4 src directory
 * Mirrored after Composer dump-autoload for performance
 *
 * @param string $class - Name of class to load.
 *
 * @since 4.4.0
 *
 * @return void
 */
function simple_links_autoload( $class ) {
	$classes = array(
		// widgets.
		'Simple_Links__Widgets__Simple_Links' => 'Widgets/Simple_Links.php',
	);
	if ( isset( $classes[ $class ] ) ) {
		require __DIR__ . '/src/' . $classes[ $class ];

		return;
	}

	if ( file_exists( SIMPLE_LINKS_DIR . 'classes/' . $class . '.php' ) ) {
		require SIMPLE_LINKS_DIR . 'classes/' . $class . '.php';
	}
}

spl_autoload_register( 'simple_links_autoload' );


$simple_links = simple_links();
/**
 * Load the plugin
 */
function simple_links_load() {
	// Because front-end builders like "Beaver Builder" won't load the required classes.
	require_once ABSPATH . 'wp-admin/includes/class-walker-category-checklist.php';
	require_once ABSPATH . 'wp-admin/includes/template.php';

	Simple_Links_Categories::get_instance();
	Simple_Links_WP_Links::init();

	add_action( 'init', array( 'Simple_Link', 'register_post_type' ) );

	if ( is_admin() ) {
		Simple_Links_Settings::init();
		Simple_Links_Sort::init();
		simple_links_admin::init();
	}
}

add_action( 'plugins_loaded', 'simple_links_load' );

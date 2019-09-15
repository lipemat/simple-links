<?php

/**
 * Misc Functions for the Simple Links Plugin
 *
 * @author OnPoint Plugins <support@onpointplugins.com>
 *
 */

/**
 * simple_links_admin
 *
 *
 * @return \simple_links_admin
 */
function simple_links_admin(){
	global $simple_links_admin_func;
	if( empty( $simple_links_admin_func ) ){
		$simple_links_admin_func = simple_links_admin::get_instance();
	}
	return $simple_links_admin_func;
}

/**
 * simple_links
 *
 *
 * @return \simple_links
 */
function simple_links(){
	global $simple_links;
	if( empty( $simple_links ) ){
		$simple_links = new simple_links();
	}

	return $simple_links;
}




/**
 * Simple Links Questions
 *
 * Creates a question mark icon of the tooltips
 *
 * @param string $id used to select with jquery
 *
 */
function simple_links_questions( $id ){
	printf( ' <img src="%squestion.png" id="%s">', SIMPLE_LINKS_IMG_DIR, $id );
}

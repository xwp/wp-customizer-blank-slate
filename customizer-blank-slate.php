<?php
/**
 * Plugin Name: Customizer Blank Slate
 * Version: 0.1.0
 * Description: Remove all constructs from being registered in the customizer, leaving you to include only the ones desired.
 * Plugin URI: https://github.com/xwp/wp-customizer-blank-slate
 * Author: Weston Ruter
 * Author URI: https://make.xwp.co/
 *
 * Copyright (c) 2016 XWP (https://make.xwp.co/)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @package CustomizeBlankSlate
 */

namespace CustomizerBlankSlate;

const QUERY_PARAM_NAME = 'customizer_blank_slate';
const QUERY_PARAM_VALUE = 'on';

// Short-circuit if customizer_blank_slate=on query param is not present.
if ( ! isset( $_GET[ QUERY_PARAM_NAME ] ) || QUERY_PARAM_VALUE !== wp_unslash( $_GET[ QUERY_PARAM_NAME ] ) ) {
	return;
}

add_filter( 'customize_loaded_components', function() {
	/*
	 * Note the customize_register action is triggered in
	 * WP_Customize_Manager::wp_loaded() which is itself the
	 * callback for the wp_loaded action at priority 10. So
	 * this wp_loaded action just has to be added at a
	 * priority less than 10.
	 */
	$priority = 1;
	add_action( 'wp_loaded', function() {

		global $wp_customize;

		/*
		 * Remove all constructs from being registered,
		 * whether in core, themes, or plugins.
		 */
		remove_all_actions( 'customize_register' );

		/*
		 * Register the panel, section, and control types that would normally have  been
		 * registered at customizer_register by WP_Customize_Manager::register_controls().
		 */
		$wp_customize->register_panel_type( 'WP_Customize_Panel' );
		$wp_customize->register_section_type( 'WP_Customize_Section' );
		$wp_customize->register_section_type( 'WP_Customize_Sidebar_Section' );
		$wp_customize->register_control_type( 'WP_Customize_Color_Control' );
		$wp_customize->register_control_type( 'WP_Customize_Media_Control' );
		$wp_customize->register_control_type( 'WP_Customize_Upload_Control' );
		$wp_customize->register_control_type( 'WP_Customize_Image_Control' );
		$wp_customize->register_control_type( 'WP_Customize_Background_Image_Control' );
		$wp_customize->register_control_type( 'WP_Customize_Cropped_Image_Control' );
		$wp_customize->register_control_type( 'WP_Customize_Site_Icon_Control' );
		$wp_customize->register_control_type( 'WP_Customize_Theme_Control' );

		/*
		 * Now register your own customize_register
		 * callback which will register just the specific
		 * panels, sections, controls, settings, etc
		 * that are relevant. This can either be done
		 * at a location as follows:
		 *
		 * add_action( 'customize_register', … );
		 *
		 * Or it can be done via a new wp_loaded
		 * handler at priority 9.
		 */
	}, $priority );

	// Short-circuit widgets, nav-menus, etc from being loaded.
	$components = array();

	return $components;
} );

// Inject the customizer_blank_slate=on query param into the initial preview URL.
add_action( 'customize_controls_init', function() {
	global $wp_customize;
	$wp_customize->set_preview_url(
		add_query_arg(
			array( QUERY_PARAM_NAME => 'on' ),
			$wp_customize->get_preview_url()
		)
	);
} );

// Enqueue the script to persist the customizer_blank_slate=on query param on all previewed URLs.
add_action( 'customize_controls_enqueue_scripts', function() {
	$handle = 'customizer-blank-slate';
	$src = plugins_url( 'customizer-blank-slate.js', __FILE__ );
	$deps = array( 'customize-controls' );
	$ver = false;
	$in_footer = true;
	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );

	$args = array(
		'queryParamName' => QUERY_PARAM_NAME,
		'queryParamValue' => QUERY_PARAM_VALUE,
	);
	wp_add_inline_script(
		$handle,
		sprintf( 'CustomizerBlankSlate.init( %s );', wp_json_encode( $args ) ),
		'after'
	);
} );

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

// Short-circuit if customizer_blank_slate=on query param is not present.
if ( ! isset( $_GET[ QUERY_PARAM_NAME ] ) || 'on' !== $_GET[ QUERY_PARAM_NAME ] ) {
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

		/*
		 * Remove all constructs from being registered,
		 * whether in core, themes, or plugins.
		 */
		remove_all_actions( 'customize_register' );

		/*
		 * Now register your own customize_register
		 * callback which will register just the specific
		 * panels, sections, controls, settings, etc
		 * that are relevant. This can either be done
		 * at a location as follows or it can be done
		 * via a new wp_loaded handler at priority 9.
		 */
		// @todo add_action( 'customize_register', … );
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

// Persist the customizer_blank_slate=on query param on all previewed URLs.
add_action( 'customize_controls_print_footer_scripts', function() {
	?>
	<script>
		(function ( api, $ ) {
			'use strict';

			var queryParamName = <?php echo wp_json_encode( QUERY_PARAM_NAME ) ?>;

			api.bind( 'ready', function() {

				// Make sure that all previewed URLs include the customize_blank_slate query param.
				var previousValidate = api.previewer.previewUrl.validate;
				api.previewer.previewUrl.validate = function injectQueryParam( url ) {
					var queryString, queryParams = {}, urlParser, validatedUrl;
					validatedUrl = previousValidate.call( this, url );

					// Parse the query params.
					urlParser = document.createElement( 'a' );
					urlParser.href = validatedUrl;
					queryString = urlParser.search.substr( 1 );
					_.each( queryString.split( '&' ), function( pair ) {
						var parts = pair.split( '=', 2 );
						if ( parts[0] ) {
							queryParams[ decodeURIComponent( parts[0] ) ] = _.isUndefined( parts[1] ) ? null : decodeURIComponent( parts[1] );
						}
					} );

					// Amend the query param if not present.
					if ( 'on' !== queryParams[ queryParamName ] ) {
						queryParams[ queryParamName ] = 'on';
						urlParser.search = $.param( queryParams );
						validatedUrl = urlParser.href;
					}
					return validatedUrl;
				};
			} );

		} ( wp.customize, jQuery ));
	</script>
	<?php
} );

/* global wp, jQuery */
/* exported CustomizerBlankSlate */

var CustomizerBlankSlate = (function( api, $ ) {
	'use strict';

	var component = {
		data: {
			queryParamName: null,
			queryParamValue: null
		}
	};

	/**
	 * Initialize functionality.
	 *
	 * @param {object} args Args.
	 * @param {string} args.queryParamName  Query param name.
	 * @param {string} args.queryParamValue Query param value.
	 * @returns {void}
	 */
	component.init = function init( args ) {
		_.extend( component.data, args );
		if ( ! args || ! args.queryParamName || ! args.queryParamValue ) {
			throw new Error( 'Missing args' );
		}

		api.bind( 'ready', function() {
			component.injectPreviewUrlQueryParam();
		} );
	};

	/**
	 * Make sure that all previewed URLs include the customize_blank_slate query param.
	 *
	 * @returns {void}
	 */
	component.injectPreviewUrlQueryParam = function injectPreviewUrlQueryParam() {
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
			if ( component.data.queryParamValue !== queryParams[ component.data.queryParamName ] ) {
				queryParams[ component.data.queryParamName ] = component.data.queryParamValue;
				urlParser.search = $.param( queryParams );
				validatedUrl = urlParser.href;
			}
			return validatedUrl;
		};
	};

	return component;

}( wp.customize, jQuery ) );

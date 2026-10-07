/**
 * Demo Mode.
 *
 * Draws the buttons from this site's saved config rather than the property's,
 * since every demo site shares one property.
 *
 * @package ShareThisShareButtons
 */

/* exported DemoMode */
var DemoMode = ( function() {
	'use strict';

	return {
		/**
		 * Holds data.
		 */
		data: {},

		/**
		 * Boot plugin.
		 *
		 * @param data
		 */
		boot: function( data ) {
			this.data = data;

			if ( 'loading' === document.readyState ) {
				document.addEventListener( 'DOMContentLoaded', this.init.bind( this ) );
			} else {
				this.init();
			}
		},

		/**
		 * Initialize plugin.
		 */
		init: function() {
			var self = this;

			if ( undefined === window.__sharethis__ ) {
				return;
			}

			if ( this.data.inline ) {
				document.querySelectorAll( '.sharethis-inline-share-buttons' ).forEach( function( container, index ) {
					var config = self.normalize( self.data.inline );

					container.id = container.id || 'st-demo-inline-' + index;
					config.container = container.id;

					if ( container.dataset.url ) {
						config.url = container.dataset.url;
					}

					self.load( 'inline-share-buttons', config );
				} );
			}

			if ( this.data.sticky ) {
				this.load( 'sticky-share-buttons', this.normalize( this.data.sticky ) );
			}

			if ( this.data.gdpr ) {
				this.load( 'gdpr-compliance-tool-v2', this.normalize( this.data.gdpr ) );
			}
		},

		/**
		 * Load a product with the given config.
		 *
		 * @param product
		 * @param config
		 */
		load: function( product, config ) {
			config.enabled = true;

			window.__sharethis__.load( product, config );
		},

		/**
		 * Saved config comes back from the form as strings, so restore the
		 * booleans and numbers the buttons expect.
		 *
		 * @param value
		 */
		normalize: function( value ) {
			var self = this,
				result;

			if ( Array.isArray( value ) ) {
				return value.map( function( item ) {
					return self.normalize( item );
				} );
			}

			if ( null !== value && 'object' === typeof value ) {
				result = {};

				Object.keys( value ).forEach( function( key ) {
					result[ key ] = self.normalize( value[ key ] );
				} );

				return result;
			}

			if ( 'true' === value || 'false' === value ) {
				return 'true' === value;
			}

			if ( 'string' === typeof value && '' !== value && ! isNaN( value ) ) {
				return Number( value );
			}

			if ( 'string' === typeof value && /^\d+px$/.test( value ) ) {
				return parseInt( value, 10 );
			}

			return value;
		}
	};
} )();

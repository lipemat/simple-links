/**
 * Admin js for Simple Links Plugin
 *
 * @type {Simple_Links|*|{}}
 */
var Simple_Links = window.Simple_Links || {};

(function( $, s, i18n, config ){
	/**
	 * Simple Links Link Ordering
	 *
	 */
	s.link_ordering = {
		wrap : {},
		list : {},
		category : {},

		original_list : '',

		init : function(){
			this.wrap = $( "#simple-links-ordering-wrap" );
			if ( this.wrap.length < 1 ) {
				return;
			}

			_.bindAll( this, '_save_order', '_filter_by_cat' );

			this.list          = this.wrap.find( 'ul' );
			this.original_list = this.list.clone();
			this.category      = $( '#simple-links-sort-cat' );

			//Setup the Draggable list
			this.list.sortable(
				{
					placeholder : 'sortable-placeholder menu-item-depth-1',
					stop : function(){
						s.link_ordering._save_order( $( this ).attr( 'id' ) );
					}
				}
			);

			//the filter by Categories
			this.category.on(
				'change',
				function(){
					s.link_ordering._filter_by_cat( $( this ).val() );
				}
			);
		},

		/**
		 * Save order
		 *
		 * Runs the ajax with the new link order
		 *
		 */
		_save_order : function(){
			var data = this.list.sortable( "serialize" );
			data    += '&category_id=' + this.category.find( 'option:selected' ).val();
			$.post( config.sort_url, data, function( response ){} );

		},

		/**
		 * Filter by Cat
		 *
		 * Retrieve the latest 200 links within a category
		 * Then convert the sortable list to these links.
		 *
		 * This fires when a category is selected
		 *
		 * @param int cat_id
		 */
		_filter_by_cat : function( cat_id ){
			if ( cat_id == 0 ) {
				this.list.html( this.original_list.html() );
				return;
			}

			var data = {
				'category_id' : cat_id
			};

			$.post(
				config.get_by_category_url,
				data,
				function( response ){
					s.link_ordering.wrap.html( response );
					s.link_ordering.list = s.link_ordering.wrap.find( 'ul' );
					s.link_ordering.list.sortable(
						{
							placeholder : 'sortable-placeholder menu-item-depth-1',
							stop : function(){
								s.link_ordering._save_order( $( this ).attr( 'id' ) );
							}
						}
					);
				}
			);
		}
	};

	/**
	 * Easter Egg
	 *
	 * @type {{init: Function}}
	 */
	s.easter = {
		init : function(){
			$( '.simple-links-title' ).change(
				function(){
					if ( $( this ).val() == "Simple Links" ) {
						for ( var i = 0; i < 10; i++ ) {
							$( this ).css( {'box-shadow' : '0px 0px 10px ' + i + 'px yellow'} );
						}
							$( this ).after( '<h2><center>HALLELUJAH!!</center></h2>' );
					}
				}
			);
		}
	};

	$(
		function(){
				s.link_ordering.init();
				s.easter.init();
		}
	);

})( jQuery, Simple_Links, SL_locale, simple_links_sort );

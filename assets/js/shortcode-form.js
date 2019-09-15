/**
 *
 * The jquery required for the shortcode MCE Form
 *
 * @TODO Cleanup the code and turn it into an array of checkboxes and values
 *
 * @author Mat Lipe <support@onpointplugins.com>
 */
var SimpleLinksShortcodeObj = {
	filters: [],
	/**
	 * Append this using $( document ).on( "simple-links-js-form-output")
	 * to adjust the final output of the shortcode
	 */
	output: '[simple-links',

	init: function () {
		//nothing to see here
	},

	add_filter: function (callback) {
		this.filters.push(callback);
	},

	apply_filters: function (value) {
		this.filters.forEach(function (callback) {
			value = callback(value);
		});
		return value;
	},

	//The function with sends the new output back to the editor and closes the popup
	insert: function ($output) {
		$output = this.apply_filters( $output );
		tinyMCEPopup.execCommand('mceReplaceContent', false, $output);
		tinyMCEPopup.close();
	}
};

//Tinymce 4 (WP 4.8) no longer has nor requires this method
if( typeof( tinyMCEPopup.onInit ) !== 'undefined' ){
	//Initiate the object This is required
	tinyMCEPopup.onInit.add( SimpleLinksShortcodeObj.init, SimpleLinksShortcodeObj );
}


//The Jquery which grabs the form data
jQuery( document ).ready( function( $ ){

	var fields = ['count', 'orderby', 'order', 'title'];

	//Generate the Code
	$( '#generate' ).click( function(){

		//Go through the standard fields
		for( var i = 0; i < fields.length; i++ ){
			//Add the standard fields to the output if they have a value
			if( $( '#' + fields[i] ).val() != '' ){
				SimpleLinksShortcodeObj.output += ' ' + fields[i] + '="' + $( '#' + fields[i] ).val() + '"';
			}
		}

		//Add the checked categories
		var cats = '';
		$( '.cat:checked' ).each( function(){
			if( cats == '' ){
				cats = ' category="';
				cats += $( this ).val();
			} else {
				cats += ',' + $( this ).val();
			}
		} );

		//Close the attribute and add it ot the shortcode
		if( cats != '' ){
			cats += '"';
			SimpleLinksShortcodeObj.output += cats;
		}

		//Add the additional fields
		var addFields = '';
		$( '.additional:checked' ).each( function(){
			if( addFields == '' ){
				addFields = ' fields="';
				addFields += $( this ).val();
			} else {
				addFields += ',' + $( this ).val();
			}
		} );
		//Close the fields
		if( addFields != '' ){
			addFields += '"';
			SimpleLinksShortcodeObj.output += addFields;
		}

		//Add the separator
		if( $( '#separator' ).val() != '-' ){
			SimpleLinksShortcodeObj.output += ' separator="' + $( '#separator' ).val() + '"';
		}

		//Add the image to the shortcode
		if( $( '#show_image' ).is( ':checked' ) ){
			SimpleLinksShortcodeObj.output += ' show_image="true"';
			if( $( '#image-size' ).val() != '' ){
				SimpleLinksShortcodeObj.output += ' image_size="' + $( '#image-size' ).val() + '"';
			}

			//Add the show Image only
			if( $( '#show_image_only' ).is( ':checked' ) ){
				SimpleLinksShortcodeObj.output += ' show_image_only="true"';
			}

		}

		//Add the description to the shortcode
		if( $( '#description' ).is( ':checked' ) ){
			SimpleLinksShortcodeObj.output += ' description="true"';
		}

		//Add the description to the shortcode
		if( $( '#description-formatting' ).is( ':checked' ) ){
			SimpleLinksShortcodeObj.output += ' show_description_formatting="true"';
		}

		//Add the line break to the code
		if( $( '#line_break' ).is( ':checked' ) ){
			SimpleLinksShortcodeObj.output += ' remove_line_break="true"';
		}

		//Add the child categories to the shortcode
		if( $( '#child-categories' ).is( ':checked' ) ){
			SimpleLinksShortcodeObj.output += ' include_child_categories="true"';
		}

		//add custom values here by using a $(document).on('simple-links-js-form-output', function(o){});
		$( document ).trigger( 'simple-links-js-form-output', [SimpleLinksShortcodeObj.output] );
		SimpleLinksShortcodeObj.output += ']';

		//Send the shortcode back to the editor
		SimpleLinksShortcodeObj.insert( SimpleLinksShortcodeObj.output );
	} );

} );

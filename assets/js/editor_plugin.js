/**
 * The MCE plugin to add Simple LInks Shortcodes
 * @author Mat Lipe <support@onpointplugins.com>
 */

/**
 * Use the global "translated" version when available.
 * Some plugins e.g. Elementor load the editor plugins later and therefore do not
 * honor `wp_localize_script` and need these defaults to prevent errors.
 *
 * @fixes 4022
 * @link https://onpointplugins.com/plugin-support/elementor/
 *
 */
var SL_locale = window.SL_locale || {
	add_links : "Add Simple Links",
	shortcode : "Simple Links Shortcode",
	shortcode_generator : "Simple Links Shortcode Generator"
};

(function( i18n ){
	tinymce.create(
		'tinymce.plugins.simpleLinks',
		{
			init : function( ed, url ){
				ed.addButton(
					'simpleLinks',
					{    //The buttons name and title and icon
						title : i18n.add_links,
						image : url + '/../img/mce-icon.png',
						cmd : 'mceHighlight' //Match the addCommand
					}
				);

				// Register commands
				ed.addCommand(
					'mceHighlight',
					function(){
						ed.windowManager.open(
							{
								file : ed.documentBaseUrl.replace( 'wp-admin/', '' ) + '?simple_links_shortcode=form',
								width : 550 + parseInt( ed.getLang( 'highlight.delta_width', 0 ) ),
								height : 650 + parseInt( ed.getLang( 'highlight.delta_height', 0 ) ),
								inline : 1,
								title : i18n.shortcode
							},
							{

								plugin_url : url

							}
						);

					}
				);

			}, createControl : function( n, cm ){
				return null;

			}, getInfo : function(){  //The plugin Buttons Details
				return {
					longname : i18n.shortcode_generator,
					author : 'Mat Lipe',
					authorurl : 'https://onpointplugins.com',
					inforurl : 'https://onpointplugins.com',
					version : '2.0.1'
				};
			}
		}
	);
	tinymce.PluginManager.add( 'simpleLinks', tinymce.plugins.simpleLinks );  //Name it the same as above
})( SL_locale );

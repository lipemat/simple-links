<?php
/**
 * The Form for the MCE Shortcode Generator
 *
 * @uses   called with a template redirect using a query var send from the mce plugin
 * @see    simple_links->load_shortcode_form();
 * @see    js/editor_plugin.js
 *
 */

?>
<title><?php esc_html_e( 'Create [simple-links] shortcode', 'simple-links' ); ?></title>
<?php
wp_head();
?>

<style type="text/css">

	<?php
	if ( get_bloginfo( 'version' ) >= 3.8 ) {
		?>
	html {
		margin-top: 46px !important;
	}

	body {
		margin-top: -66px;
		important;
	}

		<?php
	} else {
		?>
	html {
		margin-top: 28px !important;
	}

	body {
		margin-top: -23px;
		important;
	}

		<?php
	}
	?>
</style>

<body>
<div class="wrap">
	<label><?php esc_html_e( 'Title', 'simple-links' ); ?>:
		<br/>
		<input type="text" id="title" size="50"/>
	</label>

	<fieldset>
		<legend><?php esc_html_e( 'Categories', 'simple-links' ); ?></legend>
		<ul class="sl-categories">
			<?php
			$cats = Simple_Links_Categories::get_category_names();
			if ( ! empty( $cats ) ) {
				$term_args = array(
					'walker'        => new Simple_Links_Category_Checklist(),
					'taxonomy'      => Simple_Links_Categories::TAXONOMY,
					'checked_ontop' => false,
				);

				wp_terms_checklist( 0, $term_args );

			} else {
				esc_html_e( 'No link categories have been created yet.', 'simple-links' );
			}
			?>
		</ul>

	</fieldset>

	<p>
		<label><?php esc_html_e( 'Include links assigned to child categories of selected categories as well as selected categories.', 'simple-links' ); ?>
			<input
				type="checkbox"
				id="child-categories"
				value="true"/>
		</label>
	</p>

	<?php do_action( 'simple-links/shortcode-form/categories-box' ); ?>

	<hr>

	<p>
		<label><?php esc_html_e( 'Number Of Links', 'simple-links' ); ?>:
			<select id="count">
				<option value=""><?php esc_html_e( '- all -', 'simple-links' ); ?></option>
				<?php
				for ( $i = 1; $i < 200; $i ++ ) {
					printf( '<option value="%s">%s</option>', esc_attr( $i ), esc_html( $i ) );
				}
				?>
			</select>
		</label>
	</p>


	<p>
		<label><?php esc_html_e( 'Order by', 'simple-links' ); ?>:
			<select id="orderby">
				<option value=""><?php esc_html_e( '- select an order by - ', 'simple-links' ); ?></option>
				<?php
				simple_links::orderby_options();
				?>
			</select>
		</label>
	</p>

	<p>
		<label><?php esc_html_e( 'Order', 'simple-links' ); ?>:
			<select id="order">
				<option value=""><?php esc_html_e( '- select an order -', 'simple-links' ); ?></option>
				<option value="ASC"><?php esc_html_e( 'Ascending', 'simple-links' ); ?></option>
				<option value="DESC"><?php esc_html_e( 'Descending', 'simple-links' ); ?></option>
			</select>
		</label>
	</p>

	<hr>

	<p>
		<label><?php esc_html_e( 'Show description', 'simple-links' ); ?>
			<input type="checkbox" id="description" value="true"/>
		</label>
	</p>

	<p>
		<label><?php esc_html_e( 'Display description as paragraphs', 'simple-links' ); ?>
			<input
				type="checkbox"
				id="description-formatting"
				value="true"/>
		</label>
	</p>

	<hr>

	<p>
		<label><?php esc_html_e( 'Show images', 'simple-links' ); ?>
			<input
				type="checkbox"
				id="show_image"
				value="true"/>
		</label>
	</p>

	<p>
		<label><?php esc_html_e( 'Hide link title', 'simple-links' ); ?>
			<input
				type="checkbox"
				id="show_image_only"
				value="true"/>
		</label>

	</p>

	<p>
		<label>
			<?php esc_html_e( 'Image size', 'simple-links' ); ?>
			<select id="image-size">
				<?php
				foreach ( simple_links()->image_sizes() as $size ) {
					printf( '<option value="%s">%s</a>', esc_attr( $size ), esc_html( $size ) );
				}
				?>
			</select>
		</label>
	</p>

	<p>
		<label><?php esc_html_e( 'Remove line break between image and link', 'simple-links' ); ?>
			<input
				type="checkbox"
				id="line_break"
				value="1"/>
		</label>
	</p>

	<fieldset>
		<legend><?php esc_html_e( 'Additional fields', 'simple-links' ); ?></legend>

		<?php
		$fields = simple_links()->get_additional_fields();
		if ( empty( $fields ) ) {
			echo '<em>' . esc_html__( 'No additional fields have been added to settings.', 'simple-links' ) . '</em>';
		} else {
			?>
			<ul>
				<?php
				foreach ( $fields as $field ) {
					printf( '<li><label>%1$s<input class="additional" type="checkbox" value="%1$s"></label></li>', esc_attr( $field ) );
				}
				?>
			</ul>
			<?php
		}
		?>
		<label><?php esc_html_e( 'Field Separator', 'simple-links' ); ?>:
			<br/>
			<input type="text" value="-" id="separator" size="50"/>
		</label>
	</fieldset>


	<?php do_action( 'simple_links_shortcode_form' ); ?>

	<p>
		<input type="button" id="generate" class="button-primary" value="Generate">
	</p>

</div>
</body>




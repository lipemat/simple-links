<?php
$categories = get_terms( Simple_Links_Categories::TAXONOMY );
?>
<div class="wrap">
	<h2>
		<?php esc_html_e( 'Keeping Your Links In Order', 'simple-links' ); ?>!
	</h2>

	<?php
	if ( is_array( $categories ) ) {
		?>
		<h3>
			<?php esc_html_e( 'Select a link category to sort links in that category only ( optional )', 'simple-links' ); ?>
		</h3>
		<p class="description">
			<?php esc_html_e( 'When setting up your short-codes and/or widgets, selecting a single category and Order By: "Link Order" will allow the links to display in the order they were sorted in that category.', 'simple-links' ); ?>
		</p>

		<?php do_action( 'simple-links-ordering-description' ); ?>

		<select id="simple-links-sort-cat">
			<option value="0">
				<?php esc_html_e( 'All Categories', 'simple-links' ); ?>
			</option>

			<?php
			foreach ( $categories as $_cat ) {
				printf( '<option value="%s">%s</option>', esc_attr( $_cat->term_id ), esc_html( $_cat->name ) );
			}
			?>
		</select>
		<?php

	} else {
		?>
		<h3>
			<?php esc_html_e( 'To sort by link categories, you must add some links to them', 'simple-links' ); ?>.
			<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=' . Simple_Links_Categories::TAXONOMY . '&post_type=' . SIMPLE_LINK::POST_TYPE ) ); ?>">
				<?php esc_html_e( 'Follow Me', 'simple-links' ); ?>
			</a>
		</h3>
		<?php
	}
	?>
	<div id="simple-links-ordering-wrap">
		<?php
		require SIMPLE_LINKS_DIR . 'admin-views/draggable-links.php';
		?>
	</div>
</div>

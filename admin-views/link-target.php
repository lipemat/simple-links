<p>
	<label for="link_target_blank" class="selectit">
		<input id="link_target_blank" type="radio" name="target" value="_blank" <?php checked( $target, '_blank' ); ?>>
		<code>
			_blank</code> &minus; <?php _e( 'new window or tab', 'simple-links' ); ?>.
	</label>
</p>
<p>
	<label for="link_target_top" class="selectit">
		<input id="link_target_top" type="radio" name="target" value="_top" <?php checked( $target, '_top' ); ?>>
		<code>
			_top</code> &minus; <?php _e( 'current window or tab, with no frames', 'simple-links' ); ?>.
	</label>
</p>
<p>
	<label for="link_target_none" class="selectit">
		<input id="link_target_none" type="radio" name="target" value="" <?php checked( $target, '' ); ?>>
		<code>
			_none</code> &minus; <?php _e( 'same window or tab', 'simple-links' ); ?>.
	</label>
</p>

<?php
if ( isset( $this->meta_box_descriptions['target'] ) ) {
	echo '<p>' . $this->meta_box_descriptions['target'] . '</p>';
}
?>
<p>
	<input
		id="link_target_nofollow"
		type="checkbox"
		name="link_target_nofollow"
		value="1"
		<?php checked( get_post_meta( $post->ID, 'link_target_nofollow', true ), 1 ); ?>
		>

	<?php _e( 'Add a', 'simple-links' ); ?>
	<code>
		nofollow</code>
	<?php _e( 'rel to this link', 'simple-links' ); ?>
</p>

<h4><?php _e( 'These settings will effect the default WordPress Links Manager', 'simple-links' ); ?></h4>
<ul>
	<li>
		<?php _e( 'Remove default WordPress Links Manager', 'simple-links' ); ?>:
		<input type="checkbox" name="sl-remove-links" <?php checked( get_option( 'sl-remove-links' ) ); ?> value="1"/>
		<?php simple_links_questions( 'SL-remove-links' ); ?>
	</li>
	<li>
		<div class="sl-updated" id="import-links-success" style="display:none">
			<p><?php _e( 'The links have been imported successfully', 'simple-links' ); ?>

			</p>
		</div>
		<div>
			<img id="sl-import-loading" style="display: none" src="<?php echo get_bloginfo( 'url' ); ?>/wp-includes/js/thickbox/loadingAnimation.gif"/>
			<p>
				<?php
				submit_button( __( 'Import Links', 'simple-links' ), 'secondary', 'sl-import-links', false );

				?>
				&nbsp;
				<?php

				simple_links_questions( 'SL-import-links' );
				?>
			</p>
		</div>
	</li>
</ul>
  

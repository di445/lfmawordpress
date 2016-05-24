<?php
	global $writeshare;
	
	?>
	
	<form method="get" class="writeshare-filters">
		<input type="text" name="s" value="<?php echo esc_attr( empty( $_GET['s'] ) ? '' : $_GET['s'] ); ?>" placeholder="Search">

		<hr />

		<style>
			.taxonomies select, .author select {
				width: 100%;
			}
		</style>

		<div class="taxonomies">
			<?php
				foreach ( $writeshare->settings->get( 'taxonomies' ) as $taxonomy ):
					if ( !empty( $taxonomy['serial'] ) )
						continue;

			?>
					<h3><?php esc_html_e( $taxonomy['limit'] == 1 ? $taxonomy['singular'] : $taxonomy['plural'] ); ?></h3>

					<select name="<?php echo esc_attr( $taxonomy['id'] ); ?>">
						<option></option>
						<?php
							foreach( get_terms( $taxonomy['id'] ) as $term ) {
								$selected = $term->slug == ( isset( $_GET[$taxonomy['id']] ) ? $_GET[$taxonomy['id']] : '' );
								printf( '<option value="%s"%s>%s</option>', esc_attr( $term->slug ), selected( $selected, true, false ), esc_html( $term->name ) );
							}
						?>
					</select>

			<?php endforeach; ?>
		</div>

		<div class="author">
			<h3><?php esc_html_e( 'Author' ); ?></h3>

			<select name="author">
				<option></option>
				<?php
					foreach ( get_users() as $user ) {
						if ( !$user->has_cap( 'wpws_publish_content' ) )
							continue;
						$selected = $user->ID == ( isset( $_GET['author'] ) ? $_GET['author'] : '' );
						printf( '<option value="%s"%s">%s</option>', esc_attr( $user->ID ), selected( $selected, true, false ), esc_html( $user->display_name . ' (' . $user->user_nicename . ')' ) );
					}
				?>
			</select>
		</div>

		<script type="text/javascript">
			jQuery( '.taxonomies select, .author select' ).each( function( _, e ) {
				jQuery( this ).select2();
			} );
		</script>

		<hr />

		<input type="submit" value="Search">
	</form>

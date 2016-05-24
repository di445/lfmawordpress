<?php
	global $writeshare;
	
	$post = $writeshare->templates->tags->get_content();

	$title = ( !empty( $_POST['title'] ) ) ? wp_strip_all_tags( stripslashes( $_POST['title'] ) ) : ( $post ? $post->post_title : '' );
	$content = ( !empty( $_POST['content'] ) ) ? stripslashes( $_POST['content'] ) : ( $post ? $post->post_content : __( 'Start writing...', WriteShare::$TEXTDOMAIN ) );

	?>
		<div class="writeshare-page-write">

			<div class="notifications">
				<?php
					foreach ( $writeshare->routes->get_errors() as $error ) {
						printf( '<p class="error">%s</p>', $error );
					}
				?>
			</div>

			<form method="post">
				<input type="hidden" name="post_id" value="<?php echo esc_attr( ( $post ) ? $post->ID : '' ); ?>" />

				<p><input type="text" name="title" placeholder="<?php _e( 'Enter title here', WriteShare::$TEXTDOMAIN ); ?>" value="<?php echo esc_attr( $title ); ?>"/></p>

				<div>
					<?php
						wp_editor(
							$content,
							'writeshare-editor',
							array( 'textarea_name' => 'content' )
						);
					?>
				<div>

				<hr />

				<style>
					.taxonomies select {
						width: 100%;
					}
				</style>

				<div class="taxonomies">
					<?php
						foreach ( $writeshare->settings->get( 'taxonomies' ) as $taxonomy ):

					?>
							<h3><?php esc_html_e( $taxonomy['limit'] == 1 ? $taxonomy['singular'] : $taxonomy['plural'] ); ?></h3>

							<?php if ( !empty( $taxonomy['serial'] ) ): ?>
								<?php
									$taxonomy['terms'] = ( !empty( $_POST['taxonomy-' . $taxonomy['id']] ) ) ? array( stripslashes( $_POST['taxonomy-' . $taxonomy['id']] ) ) : ( $post ? (array)wp_get_object_terms( $post->ID , $taxonomy['id'], array( 'fields' => 'names' ) ) : array() );
								?>
								<p class="description"><?php _e( 'Format as a numerical version, for example "1", "1.1", "1.5.2", etc.', WriteShare::$TEXTDOMAIN ); ?></p>
								<p><input type="text" name="taxonomy-<?php echo esc_attr( $taxonomy['id'] ); ?>" value="<?php echo esc_attr( empty( $taxonomy['terms'] ) ? '' : $taxonomy['terms'][0] ); ?>" /></p>
							<?php else: ?>
								<?php
									$taxonomy['terms'] = ( !empty( $_POST['taxonomy-' . $taxonomy['id']] ) ) ? stripslashes_deep( $_POST['taxonomy-' . $taxonomy['id']] ) : ( $post ? wp_get_object_terms( $post->ID , $taxonomy['id'], array( 'fields' => 'slugs' ) ) : array() );
								?>
								<select data-label="<?php echo esc_attr( $taxonomy['limit'] == 1 ? $taxonomy['singular'] : $taxonomy['plural'] ); ?>"
									data-limit="<?php echo intval( $taxonomy['limit'] ); ?>"
									data-custom="<?php echo empty( $taxonomy['access'] ) ? 1 : 0; ?>"
									name="taxonomy-<?php echo esc_attr( $taxonomy['id'] ); ?>[]"
									<?php if ( intval( $taxonomy['limit'] ) != 1 || empty( $taxonomy['access'] ) ) echo 'multiple="true"'; ?>>

									<?php
										if ( !empty( $taxonomy['owned'] ) ) {
											$meta = array( array( 'key' => 'owner', 'value' => get_current_user_id() ) );
											$terms = get_terms( $taxonomy['id'], array( 'hide_empty' => false, 'meta_query' => $meta ) );

											/** Tack on the added terms */
											if ( !empty( $_POST['taxonomy-' . $taxonomy['id']] ) )
												foreach ( $_POST['taxonomy-' . $taxonomy['id']] as $term ) {
													if ( in_array( $term, array_map( function ( $e ) { return $e->name; }, $terms ) ) )
														continue;
													if ( in_array( $term, array_map( function ( $e ) { return $e->slug; }, $terms ) ) )
														continue;
													$_term = new StdClass();
													$_term->slug = $term;
													$_term->name = $term;
													array_unshift( $terms, $_term );
												}
										} else {
											$terms = get_terms( $taxonomy['id'], array( 'hide_empty' => false ) );
										}

										foreach ( $terms as $term ) {
											$selected = in_array( $term->slug, (array)$taxonomy['terms'] );
											printf( '<option value="%s"%s>%s</option>', esc_attr( $term->slug ), selected( $selected, true, false ), esc_html( $term->name ) );
										}
									?>

								</select>
							<?php endif; ?>
					
					<?php endforeach; ?>
				</div>

				<script type="text/javascript">
					jQuery( '.taxonomies select' ).each( function( _, e ) {
						e = jQuery( this );
						
						var limit = parseInt( e.attr( 'data-limit' ) );
						var custom = e.attr( 'data-custom' ) > 0;
						
						var options = {
						}


						if ( custom ) {
							options['multiple'] = true;
							options['tags'] = true;
							options['maximumSelectionLength'] = limit;
						} else if ( limit > 1 ) {
							options['maximumSelectionLength'] = limit;
						}

						e.select2( options );
					} );
				</script>

				<hr />

				<p>
					<input type="submit" name="publish" value="<?php esc_attr_e( 'Publish', WriteShare::$TEXTDOMAIN ); ?>"/>
					<input type="submit" name="save" value="<?php esc_attr_e( 'Save as Draft', WriteShare::$TEXTDOMAIN ); ?>"/>
					<input type="submit" name="trash" onclick="return confirm( <?php echo esc_attr( json_encode( __( 'Are you sure you want to delete this post?', WriteShare::$TEXTDOMAIN ) ) ); ?> );" value="<?php esc_attr_e( 'Delete', WriteShare::$TEXTDOMAIN ); ?>"/>
				</p>
			</form>
		</div>

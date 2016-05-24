<?php
	global $writeshare;

	?>
		<style>
			.writeshare-content-meta {
				background: #f1f1f1;
				border: 1px solid #eeeeee;
				margin-bottom: 2em;
				padding: 1em;
			}
		</style>

		<div class="writeshare-content-meta">
			<div>
				<?php echo esc_html( $writeshare->templates->tags->get_content_label() ); ?>
				written on <?php the_date(); ?>
				by <?php $writeshare->templates->tags->display_author(); ?>

				<?php
					if ( get_the_author_meta( 'ID' ) == get_current_user_id() )
						printf( '(<a href="%s">Edit</a>)', esc_attr( site_url( $writeshare->routes->slugs['write'] . '/' . get_the_ID() ) ) );
				?>
			</div>

			<div>
				<?php
					foreach ( $writeshare->settings->get( 'taxonomies' ) as $taxonomy ) {
						$label = $taxonomy['limit'] == 1 ? $taxonomy['singular'] : $taxonomy['plural'];

						if ( !empty( $taxonomy['serial'] ) ) {
							$serial = wp_get_object_terms( get_the_ID(), $taxonomy['id'] );
							if ( empty( $serial ) )
								continue;

							$serial = array_pop( $serial );
							?>
								<strong><?php echo esc_html( $label ); ?></strong>:
								<select>
									<?php
										foreach ( $writeshare->templates->tags->get_serial_terms( $taxonomy['id'] ) as $term ) {
											if ( !$term ) continue;
											printf( '<option href="%s" %s>%s (%s %s)</option>',
												esc_attr( get_permalink( $term->post->ID ) ),
												selected( $term->post->ID, get_the_ID(), false ),
												esc_html( $term->post->post_title ),
												esc_html( $taxonomy['singular'] ),
												esc_html( $term->name ) );
										}
									?>
								</select>
							<?php
							continue;
						}

						$terms = wp_get_object_terms( get_the_ID(), $taxonomy['id'] );

						if ( empty( $terms ) )
							continue;

						?>
							<div class="meta-<?php echo esc_attr( $taxonomy['id'] ); ?>">
								<strong><?php echo esc_html( $label ); ?></strong>:
									<?php
										echo implode( ', ', array_map( function( $term ) use ( $writeshare ) {
											return $term->name;
										}, $terms ) );
									?>
							</div>
						<?php
					}
				?>
			</div>

			<script type="text/javascript">
				jQuery( '.writeshare-content-meta select' ).on( 'change', function() {
					window.location = jQuery( this ).find( 'option:selected' ).attr( 'href' );
				} );
			</script>

			<div>
				<?php $words = str_word_count( get_post( get_the_ID() )->post_content ); ?>
				<?php echo esc_html( number_format_i18n( $words ) ); ?> words (~<?php echo esc_html( number_format_i18n( $words / 180 ) ); ?> minutes reading time)
			</div>
		</div>

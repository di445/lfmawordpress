<?php
	global $writeshare;

	$user = $writeshare->templates->tags->get_profile_user();

	?>
		<div class="writeshare-page-profile">
			<div class="avatar">
				<?php echo get_avatar( $user->user_email ); ?>
			</div>

			<div class="notices">
					<?php

					/** The current user is viewing their own profile */
					if ( $user->ID == get_current_user_id() ) {
						if ( !current_user_can( 'wpws_publish_content' ) ) {
							if ( get_user_meta( $user->ID, 'wpws_authorship_requested', true ) ):
								?>
									<p><?php _e( 'You are currently not allowed to publish content. Your request is being reviewed by our staff.', WriteShare::$TEXTDOMAIN ); ?></p>
								<?php
							else:
								?>
									<p><?php _e( sprintf( 'You are currently not allowed to publish content. <a href="%s">Request Writer Privileges</a>.', '?wpws-request-authorship=1' ), WriteShare::$TEXTDOMAIN ); ?></p>
								<?php
							endif;
						} else {
							?>
								<p><?php _e( sprintf( '<a href="%s">Let\'s get writing...</a>', site_url( trailingslashit( $writeshare->routes->slugs['write'] ) ) ) ); ?></p>
							<?php
						}
					}
			?>
			</div>

			<div class="content">
				<h2><?php echo esc_html( $writeshare->templates->tags->get_content_label() ); ?></h2>
				<?php
					$args = array(
						'post_type' => 'wpws_content',
						'posts_per_page' => -1,
						'author' => $user->ID,
					);

					if ( $user->ID != get_current_user_id() )
						$args['post_status'] = 'publish';
					else $args['post_status'] = array( 'publish', 'draft' );

					$posts = get_posts( $args );

					if ( empty( $posts ) ) {
						?><p>Nothing to see here</p><?php
					} else {
						global $post;
						$_post = $post;
						foreach ( $posts as $post ) {
										
							$serial = $writeshare->templates->tags->get_serial_output();
							if ( $serial ) $serial .= ': ';

							?>
								<div>
									<a href="<?php echo esc_attr( get_permalink() ); ?>">
										<?php echo esc_html( $serial ); ?>
										<?php echo esc_html( get_the_title() ); ?></a>, written <?php the_modified_date(); ?>
										<?php if ( $post->post_status == 'draft' ) echo '(Draft)'; ?>
									<?php
										if ( $user->ID == get_current_user_id() )
											printf( '(<a href="%s">Edit</a>)', esc_attr( site_url( $writeshare->routes->slugs['write'] . '/' . get_the_ID() ) ) );
									?>
								</div>
							<?php
						}
						$post = $_post;
					}
				?>
			</div>
		</div>

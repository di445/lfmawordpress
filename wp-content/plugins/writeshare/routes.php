<?php
/**
 * Housing for all the routes/rewrites and template loading.
 *
 * ...and pretty much everything else while we're figuring the structure of things out.
 */

defined( 'ABSPATH' ) || exit; /** Make sure the WordPress core is loaded */

/**
 * Manages all the settings related to this plugin
 */
class WriteShare_Routes {
	/**
	 * @var WriteShare The bound WriteShare instance.
	 */
	public $writeshare;

	/**
	 * @var array The slugs used in rewriting.
	 */
	public $slugs;

	/**
	 * @var array Errors.
	 */
	public $errors;

	/**
	 * Early initialization.
	 * 
	 * @param WriteShare $writeshare The bound WriteShare instance.
	 *
	 * @return void
	 */
	public function __construct( $writeshare ) {
		$this->writeshare = $writeshare;

		/** Bare section slugs */
		$this->slugs = array(
			'profile' => 'profile',
			'write' => 'write',
		);

		$this->errors = array();
	}

	/**
	 * Initializes rewrite rules based on the set slugs.
	 *
	 * @return void
	 */
	public function init() {
		add_rewrite_rule( sprintf( '^%s/([^/]+)/?$', $this->slugs['profile'] ), 'index.php?wpws_section=profile&wpws_user_id=$matches[1]', 'top' );
		add_rewrite_rule( sprintf( '^%s/?$', $this->slugs['profile'] ), 'index.php?wpws_section=profile', 'top' );
		add_rewrite_rule( sprintf( '^%s(?:/(\d+))?/?$', $this->slugs['write'] ), 'index.php?wpws_section=write&wpws_content_id=$matches[1]', 'top' );

		/** Register our extra query vars. */
		add_filter( 'query_vars', function( $query_vars ) {
			return array_merge( $query_vars, array(
				'wpws_section', 'wpws_user_id', 'wpws_content_id',
			) );
		} );

		/** Figure out the current route and make the necessary dispatch arrangements. */
		$_this = &$this;
		add_action( 'parse_query', function( $query ) use ( $_this ) {
			if ( !$query->is_main_query() )
				return;

			if ( $query->is_search() ) {
				$query->wpws_section = 'search';
			} else if ( $query->is_post_type_archive() && $query->get( 'post_type' ) == 'wpws_content' ) {
				$query->wpws_section = 'archive';
			}

			switch ( $query->get( 'wpws_section' ) ? : $query->wpws_section ):
				case 'profile':
					$user_id = $query->get( 'wpws_user_id' );
					if ( empty( $user_id ) ) {
						if ( get_current_user_id() ) {
							wp_redirect( site_url( $_this->slugs['profile'] . '/' . wp_get_current_user()->user_login ) );
							exit;
						} else {
							wp_redirect( wp_login_url( trailingslashit( $_this->slugs['profile'] ) ) );
							exit;
						}
					}
					
					/** Author and subscriber profile pages. */
					$user = get_user_by( 'login', $query->get( 'wpws_user_id' ) );
					/** Profiles are only available for subscribers and authors */
					if ( $user )
						$user_roles = array_intersect( array( 'author', 'administrator', 'subscriber' ), $user->roles );
					if ( $user && !empty( $user_roles ) ) {
						$query->is_404 = false;
						$query->wpws_is_profile = $user;

						$_this->do_profile_view_actions( $user );
					} else {
						$query->set_404();
					}
					break;
				case 'write':
					/** The write/edit screen. */
					if ( $query->get( 'wpws_content_id' ) ) {
						$post = get_post( $query->get( 'wpws_content_id' ) );

						if ( !current_user_can( 'wpws_edit_content', $post ) ) {
							wp_redirect( wp_login_url( trailingslashit( $_this->slugs['write'] ) ) );
							exit;
						}
					}

					if ( !current_user_can( 'wpws_publish_content' ) ) {
						wp_redirect( wp_login_url( trailingslashit( is_user_logged_in() ? $_this->slugs['profile'] : $_this->slugs['write'] ) ) );
						exit;
					}

					$query->is_404 = false;
					$query->wpws_content = get_post( $query->get( 'wpws_content_id' ) );
					$query->wpws_is_write = true;

					$_this->do_write_actions();

					break;
				case 'search':
					$query->wpws_is_search = true;
					break;
				case 'archive':
					$query->wpws_is_archive = true;
					break;
			endswitch;
		} );
	}

	/**
	 * Kick off any processing stuff that we need to do on the profile page.
	 *
	 * @return void
	 */
	public function do_profile_view_actions( $user ) {

		/** Accessing own profile */
		if ( $user->ID == get_current_user_id() ) {

			/** Authorship requests */
			if ( isset( $_GET['wpws-request-authorship'] ) && !get_user_meta( $user->ID, 'wpws_authorship_requested', true ) ) {
				$this->writeshare->notifications->send( 'authorship_requested', get_option( 'admin_email' ), array( 'user' => $user ) );
				update_user_meta( $user->ID, 'wpws_authorship_requested', true );
			}
		}
	}

	/**
	 * Kick off any processing stuff that we need to do on the write page.
	 *
	 * @return void
	 */
	public function do_write_actions() {
		if ( !empty( $_POST ) ) {
			if ( !current_user_can( 'wpws_publish_content' ) )
				wp_die( __( 'You are not allowed to do this.', WriteShare::$TEXTDOMAIN ) );

			if ( !empty( $_POST['post_id'] ) && !current_user_can( 'wpws_edit_content', $post = get_post( $_POST['post_id'] ) ) )
				wp_die( __( 'You are not allowed to do this.', WriteShare::$TEXTDOMAIN ) );

			if ( isset( $_POST['trash'] ) ) {
				if ( $post )
					wp_trash_post( $post->ID );
				
				wp_redirect( site_url( $this->slugs['profile'] ) );
				exit;
			}

			if ( empty( $_POST['title'] ) )
				$this->add_error( __( 'Title cannot be empty', WriteShare::$TEXTDOMAIN ) );

			if ( empty( $_POST['content'] ) )
				$this->add_error( __( 'Content cannot be empty', WriteShare::$TEXTDOMAIN ) );

			$post_title = $_POST['title'];
			$post_content = $_POST['content'];

			$taxonomies = $this->writeshare->settings->get( 'taxonomies' );

			foreach ( $taxonomies as $taxonomy ) {
				$label = $taxonomy['limit'] == 1 ? $taxonomy['singular'] : $taxonomy['plural'];
				if ( empty( $_POST['taxonomy-' . $taxonomy['id']] ) ) {
					if ( !empty( $taxonomy['required'] ) )
						$this->add_error( __( sprintf( '%s cannot be empty', esc_html( $label ) ), WriteShare::$TEXTDOMAIN ) );
				} else {
					if ( $taxonomy['limit'] != 0 && count( $_POST['taxonomy-' . $taxonomy['id']] ) > $taxonomy['limit'] )
						$this->add_error( __( sprintf( '%s limit reached. Please remove some terms.', esc_html( $label ) ), WriteShare::$TEXTDOMAIN ) );

					if ( !empty( $taxonomy['access'] ) ) {
						/** Only admins are allowed to add new ones. */
						foreach ( (array)$_POST['taxonomy-' . $taxonomy['id']] as $term ) {
							if ( !term_exists( stripslashes( $term ), $taxonomy['id'] ) )
								$this->add_error( __( sprintf( '%s is not a valid %s', esc_html( $term ), $taxonomy['singular'] ), WriteShare::$TEXTDOMAIN ) );
						}
					}

					if ( !empty( $taxonomy['owned'] ) ) {
						foreach ( (array)$_POST['taxonomy-' . $taxonomy['id']] as $term ) {
							/** Can't add owned taxonomy not owned by the current user */
							if ( $term = term_exists( stripslashes( $term ), $taxonomy['id'] ) ) {
								if ( get_term_meta( $term['term_id'], 'owner', true ) != get_current_user_id() )
									$this->add_error( __( sprintf( '%s is not owned by you', esc_html( $term ) ), WriteShare::$TEXTDOMAIN ) );
							}
						}
						
					}
				}
			}

			$status = isset( $_POST['save'] ) ? 'draft' : 'publish';

			/** Create the content */
			if ( !$this->get_errors() ) {
				$upsert = empty( $post ) ? 'wp_insert_post' : 'wp_update_post';
				$post_id = $upsert( array(
					'ID' => empty( $post ) ? null : $post->ID,
					'post_content' => $post_content,
					'post_title' => wp_strip_all_tags( $post_title ),
					'post_type' => 'wpws_content',
					'post_author' => get_current_user_id(),
					'post_status' => $status,
				) );

				/** Update the taxonomies */
				foreach ( $taxonomies as $taxonomy ) {

					$terms = array();

					if ( empty( $taxonomy['required'] ) && empty( $_POST['taxonomy-' . $taxonomy['id']] ) )
						continue;

					foreach ( (array)$_POST['taxonomy-' . $taxonomy['id']] as $term ) {
						if ( !term_exists( stripslashes( $term ), $taxonomy['id'] ) ) {
							$_result = wp_insert_term( stripslashes( $term ), $taxonomy['id'] );
							update_term_meta( $_result['term_id'], 'owner', get_current_user_id() );
							$terms []= $_result['term_id'];
						} else {
							$terms []= $term;
						}
					}

					wp_set_object_terms( $post_id, $terms, $taxonomy['id'] );
				}

				wp_redirect( get_permalink( $post_id ) );
				exit;
			}
		}
	}


	/**
	 * Register an error for later output.
	 *
	 * @return void
	 */
	public function add_error( $message ) {
		$this->errors []= $message;
	}

	/**
	 * Retrieve errors.
	 *
	 * @return array Errors.
	 */
	public function get_errors() {
		return $this->errors;
	}
}

<?php
/**
 * Housing for all the template logic.
 */

defined( 'ABSPATH' ) || exit; /** Make sure the WordPress core is loaded */

/**
 * Manages all the templates logic. 
 */
class WriteShare_Templates {
	/**
	 * @var WriteShare The bound WriteShare instance.
	 */
	public $writeshare;

	/**
	 * @var WriteShare_Template_Tags All template tags for WriteShare.
	 */
	public $tags;

	/**
	 * Early initialization.
	 * 
	 * @param WriteShare $writeshare The bound WriteShare instance.
	 *
	 * @return void
	 */
	public function __construct( $writeshare ) {
		$this->writeshare = $writeshare;
		$this->tags = new WriteShare_Template_Tags( $this->writeshare );
	}

	/**
	 * Template logic.
	 *
	 * @return void
	 */
	public function init() {
		$_this = &$this;

		/** Register some default scripts and styles that we'd like to have. */
		wp_register_script( 'select2', plugins_url( 'js/select2.min.js', __FILE__ ), array( 'jquery' ) );
		wp_register_style( 'select2', plugins_url( 'css/select2.min.css', __FILE__ ) );

		/**
		 * Override the template loader to inject our own templates.
		 *
		 * Here's how it works. We locate the template required to be injected
		 * and inject it into the global $post and $wp_query->post.
		 */
		add_filter( 'template_include', function( $template ) use ( $_this ) {
			/** Profile view */
			if ( $_this->tags->is_profile_view() ) {
				/** Load and buffer the template */
				$template = $_this->locate( array( 'profile.php' ) );

				ob_start();
				require $template;
				$content = ob_get_clean();

				add_shortcode( 'wpws-template-profile', function( $attrs ) use ( $content ) {
					return $content;
				} );

				$_this->hijack_post( array(
					'post_title' => __( 'Profile', WriteShare::$TEXTDOMAIN ),
					'post_content' => '[wpws-template-profile]',
				) );

				return locate_template( apply_filters( 'wpws_default_page_template', array( 'page.php' ) ) );

			/** Write view */
			} else if ( $_this->tags->is_write_view() ) {
				/** Load and buffer the template */
				$template = $_this->locate( array( 'write.php' ) );

				ob_start();
				require $template;
				$content = ob_get_clean();

				/** Add select2 */
				wp_enqueue_script( 'select2' );
				wp_enqueue_style( 'select2' );

				add_shortcode( 'wpws-template-write', function( $attrs ) use ( $content ) {
					return $content;
				} );

				$_this->hijack_post( array(
					'post_title' => __( 'Write', WriteShare::$TEXTDOMAIN ),
					'post_content' => '[wpws-template-write]',
				) );

				return locate_template( apply_filters( 'wpws_default_page_template', array( 'page.php' ) ) );
				
			} else if ( $_this->tags->is_search_view() || $_this->tags->is_archive_view() ) {
				/** Load and buffer the template */
				$_template = $_this->locate( array( 'filter.php' ) );

				ob_start();
				require $_template;
				$content = ob_get_clean();

				/** Add select2 */
				wp_enqueue_script( 'select2' );
				wp_enqueue_style( 'select2' );

				add_shortcode( 'wpws-template-filters', function( $attrs ) use ( $content ) {
					return $content;
				} );

				$_this->hijack_post_append( array(
					'post_content' => '[wpws-template-filters]',
					'post_type' => 'wpws_content_filters',
					'comments_open' => false,
				) );
			}

			return $template;
		} );

		add_shortcode( 'wpws-content-meta', function( $attrs ) use ( $_this ) {
			$template = $_this->locate( array( 'meta.php' ) );

			ob_start();
			require $template;
			
			return ob_get_clean();
		} );

		add_filter( 'the_excerpt', function ( $excerpt ) {
			global $post;

			if ( !empty( $post ) && $post->post_type == 'wpws_content' ) {
				return do_shortcode( '[wpws-content-meta]' ) . $excerpt;
			}

			if ( !empty( $post ) && $post->post_type === 'wpws_content_filters' ) {
				return do_shortcode( '[wpws-template-filters]' );
			}

			return $excerpt;
		}, 9999 );

		/** Inject the meta information on top of the content */
		add_filter( 'the_content', function( $content ) {
			global $post;

			/** Suppress in excerpt output */
			foreach ( debug_backtrace() as $step )                                                                                                                      
				if ( $step['function'] == 'get_the_excerpt' )
					return $content;

			if ( !empty( $post ) && $post->post_type == 'wpws_content' ) {
				if ( is_archive() )
					return do_shortcode( '[wpws-content-meta]' ) . get_the_excerpt();
				return '[wpws-content-meta]' . $content;
			}

			return $content;
		} );
	}

	/**
	 * Locate a template file.
	 *
	 * @param array $templates A list of templates to locate.
	 *
	 * @return string The full path to the template located.
	 */
	public function locate( $templates ) {
		/**
		 * WriteShare templates are looked for in
		 * child theme, parent theme, and finally in the templates/
		 * of the plugin itself.
		 */
		$template_paths = array_unique( array(
			get_stylesheet_directory() . '/writeshare-templates',
			get_template_directory() . '/writeshare-templates',
			plugin_dir_path( __FILE__ ) . 'templates'
		) );

		foreach ( $templates as $template ) {
			foreach ( $template_paths as $template_path ) {
				if ( file_exists( trailingslashit( $template_path ) . $template ) ) {
					return trailingslashit( $template_path ) . $template;
				}
			}
		}
		
		trigger_error( sprintf( __( 'No WriteShare templates found for: %s', WriteShare::$TEXTDOMAIN ), implode( ',', $templates ) ), E_USER_ERROR );
	}

	/**
	 * Rewrite the current post.
	 *
	 * @param array $args The post arguments (passed to WP_Post).
	 *
	 * @return void
	 */
	public function hijack_post( $args ) {
		global $wp_query, $post;

		$args = wp_parse_args( $args, array(
			'filter' => 'raw',
			'post_type' => 'page'
		) );

		$post = new WP_Post( (object)$args );

		$wp_query->post = $post;
		$wp_query->posts = array( $post );
		$wp_query->post_count = 1;
	}

	public function hijack_post_append( $args ) {
		global $wp_query, $post;

		$args = wp_parse_args( $args, array(
			'filter' => 'raw'
		) );

		$post = new WP_Post( (object)$args );

		$wp_query->post = $post;
		$wp_query->posts = array_merge( array( $post ), $wp_query->posts );
		$wp_query->post_count++;
	}
}

/**
 * Contains template tags logic.
 */
class WriteShare_Template_Tags {
	/**
	 * @var WriteShare The bound WriteShare instance.
	 */
	public $writeshare;

	/**
	 * @param WriteShare $writeshare The bound WriteShare instance.
	 *
	 * @return void
	 */
	public function __construct( $writeshare ) {
		$this->writeshare = $writeshare;
	}

	public function is_profile_view() {
		global $wp_query;
		return (bool)$wp_query->wpws_is_profile;
	}

	public function get_profile_user() {
		global $wp_query;
		return $wp_query->wpws_is_profile;
	}

	public function is_write_view() {
		global $wp_query;
		return $wp_query->wpws_is_write;
	}

	public function is_search_view() {
		global $wp_query;
		return $wp_query->wpws_is_search;
	}

	public function is_archive_view() {
		global $wp_query;
		return $wp_query->wpws_is_archive;
	}

	public function get_content() {
		global $wp_query;
		return $wp_query->wpws_content;
	}

	public function get_content_label() {
		return $this->writeshare->defaults->content_types[$this->writeshare->settings->get( 'content_type' )]['label'];
	}

	public function display_author() {
		$route = $this->writeshare->routes->slugs['profile'];
		echo sprintf( '<a href="%s">%s</a>', esc_attr( site_url( $route . '/' . get_the_author_meta( 'nicename' ) . '/' ) ), esc_html( get_the_author() ) );
	}

	public function get_serial_terms( $serial_taxonomy_id ) {
		global $wpdb;

		foreach ( $this->writeshare->settings->get( 'taxonomies' ) as $taxonomy ) {
			if ( !empty( $taxonomy['owned'] ) ) {
				global $post;

				$terms = wp_get_object_terms( $post->ID, $taxonomy['id'], array( 'fields' => 'ids' ) );

				$_terms = array_map( function( $post ) use ( $serial_taxonomy_id ) {
					$terms = wp_get_object_terms( $post->ID, $serial_taxonomy_id );
					$term = empty( $terms ) ? null : array_pop( $terms );
					if ( $term ) {
						$term->post = $post;
					}
					return $term;
				}, get_posts( array(
						'post_type' => 'wpws_content',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'tax_query' => array(
							array(
								'taxonomy' => $taxonomy['id'],
								'field' => 'term_id',
								'terms' => $terms,
							),
						),
					) )
				);

				usort( $_terms, function( $a, $b ) {
					return version_compare( $a->name, $b->name );
				} );

				return $_terms;
			}
		}

		return array();
	}

	public function get_serial_output() {
		foreach ( $this->writeshare->settings->get( 'taxonomies' ) as $taxonomy ) {
			if ( !empty( $taxonomy['owned'] ) )
				$owned = $taxonomy;
			if ( !empty( $taxonomy['serial'] ) )
				$serial = $taxonomy;
		}

		$post = get_post();

		if ( empty( $owned ) || empty( $serial ) || !$post )
			return false;

		$owned_terms = get_the_terms( $post, $owned['id'] ) ? : array();
		$serial_terms = get_the_terms( $post, $serial['id'] ) ? : array();

		$owned_term = array_pop( $owned_terms );
		$serial_term = array_pop( $serial_terms );

		if ( empty( $owned_term ) || empty( $serial_term ) )
			return false;

		return $owned_term->name . ', ' . $serial['singular'] . ' ' . $serial_term->name;
	}
}

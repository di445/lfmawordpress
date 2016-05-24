<?php
/**
 * Housing for all the sane defaults for this plugin.
 */

defined( 'ABSPATH' ) || exit; /** Make sure the WordPress core is loaded */

/**
 * Manages all the defaults related to this plugin
 */
class WriteShare_Defaults {
	/**
	 * @var array The available content_types.
	 *
	 * Filtered with `wpws_content_types` filter.
	 */
	public $content_types;

	/**
	 * @var array The available page_types.
	 *
	 * Fitlered with the `wpws_page_types` filter.
	 */
	public $page_types;

	/**
	 * Initialization.
	 * 
	 * @param WriteShare $writeshare The bound WriteShare instance.
	 *
	 * @return void
	 */
	public function __construct( $writeshare ) {
		$this->writeshare = $writeshare;

		/**
		 * Default content types.
		 *
		 * Content types are merely labels that override the `wpws_content`
		 *  custom post type.
		 *
		 * The structure of each content type is as follows:
		 * - `id` A unique ID, lowercase
		 * - `label` as per https://codex.wordpress.org/Function_Reference/register_post_type#Arguments
		 * - `labels` as per https://codex.wordpress.org/Function_Reference/register_post_type#Arguments
		 * - `taxonomies` an array of template taxnomies with these arguments https://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments
		 */
		$this->content_types = array(
			'content' => array(
				'id' => 'content',
				'label' => __( 'Content', WriteShare::$TEXTDOMAIN ),

				'taxonomies' => array(
					array(
						'id' => 'category',
						'label' => __( 'Categories', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Category', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'tags',
						'label' => __( 'Tags', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Tag', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'content',
				),
			),
			'fanfic' => array(
				'id' => 'fanfic',
				'label' => __( 'Fanfic', WriteShare::$TEXTDOMAIN ),

				'taxonomies' => array(
					array(
						'id' => 'genre',
						'label' => __( 'Genres', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Genre', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'book',
						'label' => __( 'Books', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Book', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'chapter',
						'label' => __( 'Chapters', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Chapter', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'fanfic',
				),
			),
			'essay' => array(
				'id' => 'essay',
				'label' => __( 'Essays', WriteShare::$TEXTDOMAIN ),
				'labels' => array(
					'singular_name' => __( 'Essay', WriteShare::$TEXTDOMAIN ),
				),

				'taxonomies' => array(
					array(
						'id' => 'subject',
						'label' => __( 'Subjects', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Subject', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'type',
						'label' => __( 'Types', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Type', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'essays',
				),
			),
			'recipe' => array(
				'id' => 'recipe',
				'label' => __( 'Recipes', WriteShare::$TEXTDOMAIN ),
				'labels' => array(
					'singular_name' => __( 'Recipe', WriteShare::$TEXTDOMAIN ),
				),

				'taxonomies' => array(
					array(
						'id' => 'meal',
						'label' => __( 'Meals', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Meal', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'ingredient',
						'label' => __( 'Ingredients', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Ingredient', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'type',
						'label' => __( 'Types', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Type', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'recipes',
				),
			),
			'poem' => array(
				'id' => 'poem',
				'label' => __( 'Poems', WriteShare::$TEXTDOMAIN ),
				'labels' => array(
					'singular_name' => __( 'Poem', WriteShare::$TEXTDOMAIN ),
				),

				'taxonomies' => array(
					array(
						'id' => 'genre',
						'label' => __( 'Genres', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Genre', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'rating',
						'label' => __( 'Ratings', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Rating', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'poems',
				),
			),
			'dream' => array(
				'id' => 'dream',
				'label' => __( 'Dreams', WriteShare::$TEXTDOMAIN ),
				'labels' => array(
					'singular_name' => __( 'Dream', WriteShare::$TEXTDOMAIN ),
				),

				'taxonomies' => array(
					array(
						'id' => 'rating',
						'label' => __( 'Ratings', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Rating', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'dreams',
				),
			),
			'magazine' => array(
				'id' => 'magazine',
				'label' => __( 'Magazines', WriteShare::$TEXTDOMAIN ),
				'labels' => array(
					'singular_name' => __( 'Magazine', WriteShare::$TEXTDOMAIN ),
				),

				'taxonomies' => array(
					array(
						'id' => 'topic',
						'label' => __( 'Topics', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Topic', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'subject',
						'label' => __( 'Subjects', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Subject', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'magazines',
				),
			),
			'news' => array(
				'id' => 'news',
				'label' => __( 'News', WriteShare::$TEXTDOMAIN ),

				'taxonomies' => array(
					array(
						'id' => 'topic',
						'label' => __( 'Topics', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Topic', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'subject',
						'label' => __( 'Subjects', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Subject', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'news',
				),
			),
			'writing' => array(
				'id' => 'writing',
				'label' => __( 'Creative Writing', WriteShare::$TEXTDOMAIN ),

				'taxonomies' => array(
					array(
						'id' => 'type',
						'label' => __( 'Types', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Type', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'genre',
						'label' => __( 'Genres', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Genre', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'rating',
						'label' => __( 'Ratings', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Rating', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'writing',
				),
			),
			'academic' => array(
				'id' => 'academic',
				'label' => __( 'Academic', WriteShare::$TEXTDOMAIN ),

				'taxonomies' => array(
					array(
						'id' => 'type',
						'label' => __( 'Types', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Type', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'discipline',
						'label' => __( 'Disciplines', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Discipline', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'topic',
						'label' => __( 'Topics', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Topic', WriteShare::$TEXTDOMAIN ),
						),
					),
					array(
						'id' => 'subject',
						'label' => __( 'Subjects', WriteShare::$TEXTDOMAIN ),
						'labels' => array(
							'singular_name' => __( 'Subject', WriteShare::$TEXTDOMAIN ),
						),
					),
				),

				'rewrite' => array(
					'slug' => 'academic',
				),
			),
		);
	}
}

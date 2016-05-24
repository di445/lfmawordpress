<?php
/**
 * Plugin Name: WriteShare Writing Community Platform
 * Plugin URI: http://wordpress.org/extend/plugins/writeshare/
 * Version: 1.1.11
 * Description: Writing Platform for education/LMS, fanfiction, creative writing, and more.
 * Author: Fandom Entertainment LLC
 * Author URI: https://wpwriteshare.com
 * Text Domain: writeshare
 * Domain Path: /languages
 * License: GPL v3
 */

defined( 'ABSPATH' ) || exit; /** Make sure the WordPress core is loaded */

/**
 * The main WriteShare class.
 */
class WriteShare {

	/**
	 * @var string Translation textdomain.
	 */
	public static $TEXTDOMAIN = 'writeshare';

	/**
	 * @var WriteShare_Settings The settings manager.
	 */
	public $settings;

	/**
	 * @var WriteShare_Capabilities The capabilities manager.
	 */
	private $capabilities;

	/**
	 * @var WriteShare_Dashboard The dashboard manager.
	 */
	private $dashboard;

	/**
	 * @var WriteShare_Defaults The defaults manager.
	 */
	public $defaults;

	/**
	 * @var WriteShare_Routes The routes manager.
	 */
	public $routes;

	/**
	 * @var WriteShare_Templates The template manager.
	 */
	public $templates;

	/**
	 * @var WriteShare_Notifications Email notifications manager.
	 */
	public $notifications;

	/**
	 * Early initialization.
	 *
	 * Add some early action and filter hooks here. Load the necessary
	 *  supporting classes.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );

		require_once dirname( __FILE__ ) . '/settings.php';

		$this->settings = new WriteShare_Settings( $this );
		$this->settings->init();

		/** Load textdomain */
		add_action( 'plugins_loaded', function () {
			load_plugin_textdomain( WriteShare::$TEXTDOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		} );
	}

	/**
	 * Initialization.
	 *
	 * Called on the `init` WordPress action.
	 *
	 * @return void
	 */
	public function init() {
		require_once dirname( __FILE__ ) . '/capabilities.php';
		$this->capabilities = new WriteShare_Capabilities( $this );
		$this->capabilities->init();

		require_once dirname( __FILE__ ) . '/defaults.php';
		$this->defaults = new WriteShare_Defaults( $this );

		if ( is_admin() ) {
			if ( current_user_can( 'wpws_access_dashboard' ) ) {
				/** Load Dashboard Menus */
				require_once dirname( __FILE__ ) . '/dashboard.php';
				$this->dashboard = new WriteShare_Dashboard( $this );
				$this->dashboard->init();
			} else {
				/** Redirect to Home Page */
				wp_redirect( get_home_url() );
				exit;
			}
		} else {
			require_once dirname( __FILE__ ) . '/templates.php';
			$this->templates = new WriteShare_Templates( $this );
			$this->templates->init();
		}

		require_once dirname( __FILE__ ) . '/routes.php';
		$this->routes = new WriteShare_Routes( $this );
		$this->routes->init();

		require_once dirname( __FILE__ ) . '/notifications.php';
		$this->notifications = new WriteShare_Notifications( $this );

		/** Display the admin bar... or not */
		show_admin_bar( current_user_can( 'wpws_see_admin_bar' ) );


		/** Register the main content type post type */
		/** See settings.php for `wpws_content_types` and `wpws_default_content_type` filter documentation */
		$available_content_types = apply_filters( 'wpws_content_types', $this->defaults->content_types );
		$current_content_type = $this->settings->get( 'content_type', apply_filters( 'wpws_default_content_type', 'content' ) );
		$content_type = $available_content_types[$current_content_type];

		/** Clean the picked content type up */
		unset( $content_type['taxonomies'] );

		/**
		 * The `wpws_register_content_type_args` filter.
		 *
		 * Allows developers to override the arguments to the `register_post_type`
		 *  call.
		 *
		 * @param array The overrides
		 * @param string $content_type The current content type settings
		 */
		register_post_type( 'wpws_content', array_merge( apply_filters( 'wpws_register_content_type_args', array(), $content_type ),
			array_merge( $content_type, array(
				'public' => true,
				'has_archive' => true,
				'menu_icon' => 'dashicons-edit',
				'menu_position' => 5,
				'supports' => array( 'revisions', 'author', 'comments', 'editor', 'title', 'thumbnail', 'buddypress-activity' ),
				'bp_activity' => array(
					'action_id' => 'new_wpws_content',
					'comment_action_id' => 'new_wpws_content_comment',
				),
			) )
		) );

		/** Register the taxonomies */
		foreach ( $this->settings->get( 'taxonomies', array() ) as $taxonomy ) {
			/**
			 * The `wpws_register_taxonomy_args` filter.
			 *
			 * Allows developers to override the arguments to `register_taxonomy`
			 *
			 * @param array The overrides
			 * @param array $taxonomy The taxonomy as stored in the options table
			 */
			register_taxonomy( $taxonomy['id'], 'wpws_content', array_merge( apply_filters( 'wpws_register_taxonomy_args', array(), $taxonomy ),
				array(
					'label' => $taxonomy['plural'],
					'labels' => array(
						'singular_name' => $taxonomy['singular']
					),
					'hierarchical' => true,
					'rewrite' => array(
						'hierarchical' => true,
					)
				)
			) );
		}
	}
}

$writeshare = new WriteShare;

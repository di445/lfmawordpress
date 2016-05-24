<?php
/*
Plugin Name: LearnPress Course Review
Plugin URI: http://thimpress.com/learnpress
Description: Adding review for course
Author: ThimPress
Version: 1.0
Author URI: http://thimpress.com
Tags: learnpress
Text Domain: learnpress-course-review
Domain Path: /languages/
*/
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'LP_ADDON_COURSE_REVIEW_FILE', __FILE__ );
define( 'LP_ADDON_COURSE_REVIEW_PATH', dirname( __FILE__ ) );
define( 'LP_ADDON_COURSE_REVIEW_PER_PAGE', 5 );


/**
 * Class LP_Addon_Course_Review
 */
class LP_Addon_Course_Review {
	/**
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * LP_Addon_Course_Review constructor.
	 */
	function __construct() {
		if ( !function_exists( 'learn_press_load_plugin_text_domain' ) ) {
			return;
		}
		if ( self::$_instance || defined( 'LP_ADDON_COURSE_REVIEW_TMPL' ) ) {
			return;
		}

		define( 'LP_ADDON_COURSE_REVIEW_TMPL', LP_ADDON_COURSE_REVIEW_PATH . '/templates/' );
		define( 'LP_ADDON_COURSE_REVIEW_THEME_TMPL', learn_press_template_path() . '/addons/course-review/' );
		define( 'LP_ADDON_COURSE_REVIEW_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

		add_action( 'learn_press_after_the_title', array( $this, 'print_rate' ), 10, 1 );
		add_action( 'learn_press_content_landing_summary', array( $this, 'print_review' ), 80 );
		add_action( 'learn_press_content_learning_summary', array( $this, 'print_review' ), 80 );
		add_action( 'learn_press_content_learning_summary', array( $this, 'add_review_button' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'review_assets' ) );
		add_action( 'wp', array( $this, 'course_review_init' ) );
		add_action( 'parse_comment_query', array( $this, 'exclude_rating' ) );

		add_action( 'init', array( $this, 'load_text_domain' ) );


		require_once LP_ADDON_COURSE_REVIEW_PATH . '/incs/review-functions.php';

		LP_Request_Handler::register_ajax( 'add_review', array( $this, 'add_review' ) );

		self::$_instance = $this;
	}

	function print_rate() {
		learn_press_course_review_template( 'course-rate.php' );
	}

	function print_review() {
		learn_press_course_review_template( 'course-review.php' );
	}

	function add_review_button() {
		if ( !learn_press_get_user_rate( get_the_ID() ) ) {
			learn_press_course_review_template( 'review-form.php' );
		}
	}

	function review_assets() {
		if ( learn_press_is_course() ) {
			wp_enqueue_script( 'course-review', LP_ADDON_COURSE_REVIEW_URL . '/assets/course-review.js', array( 'jquery' ), '', true );
			wp_enqueue_style( 'course-review', LP_ADDON_COURSE_REVIEW_URL . '/assets/course-review.css' );
			wp_enqueue_style( 'dashicons' );
			wp_localize_script( 'course-review', 'learn_press_course_review',
				array(
					'localize' => array(
						'empty_title'   => __( 'Please enter the review title', 'learnpress-course-review' ),
						'empty_content' => __( 'Please enter the review content', 'learnpress-course-review' ),
						'empty_rating'  => __( 'Please select your rating', 'learnpress-course-review' )
					)
				)
			);
		}
	}

	function course_review_init() {
		$paged = !empty( $_REQUEST['paged'] ) ? intval( $_REQUEST['paged'] ) : 1;
		learn_press_get_course_review( get_the_ID(), $paged );
	}

	function exclude_rating( $query ) {
		$query->query_vars['type__not_in'] = 'review';
	}


	function add_review() {
		$response = array( 'result' => 'success' );
		$nonce    = !empty( $_REQUEST['review_nonce'] ) ? $_REQUEST['review_nonce'] : '';
		$id       = !empty( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : 0;
		$rate     = !empty( $_REQUEST['rating'] ) ? $_REQUEST['rating'] : '0';
		$title    = !empty( $_REQUEST['review_title'] ) ? $_REQUEST['review_title'] : '';
		$content  = !empty( $_REQUEST['review_content'] ) ? $_REQUEST['review_content'] : '';

		if ( wp_verify_nonce( $nonce, 'learn_press_course_review_' . $id ) ) {
			$response['result']  = 'fail';
			$response['message'] = __( 'Error', 'learnpress-course-review' );
		}

		if ( get_post_type( $id ) != 'lp_course' ) {
			$response['result']  = 'fail';
			$response['message'] = __( 'Invalid course', 'learnpress-course-review' );
		}

		$return              = learn_press_add_course_review(
			array(
				'user_id'   => get_current_user_id(),
				'course_id' => $id,
				'rate'      => $rate,
				'title'     => $title,
				'content'   => $content
			)
		);
		$response['comment'] = $return;
		learn_press_send_json( $response );
	}

	/**
	 * Return unique instance of LP_Addon_Course_Review object
	 */
	static function instance() {
		if ( !self::$_instance ) {
			self::$_instance = new self();

		}
		return self::$_instance;
	}

	/**
	 * Load text domain
	 */
	static function load_text_domain() {
		if( function_exists('learn_press_load_plugin_text_domain')){ learn_press_load_plugin_text_domain(LP_ADDON_COURSE_REVIEW_PATH, true ); }
	}
}

add_action( 'learn_press_ready', array( 'LP_Addon_Course_Review', 'instance' ) );



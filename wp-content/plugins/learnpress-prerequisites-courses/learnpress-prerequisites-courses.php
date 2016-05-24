<?php
/*
Plugin Name: LearnPress Prerequisite Courses
Plugin URI: http://thimpress.com/learnpress
Description: Course you have to finish before you can enroll to this course
Author: ThimPress
Version: 1.0
Author URI: http://thimpress.com
Tags: learnpress
Text Domain: learnpress-prerequisites
Domain Path: /languages/
*/

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
define( 'LP_PREREQUISITES_PLUGIN_FILE', __FILE__ );
define( 'LP_PREREQUISITES_PLUGIN_PATH', dirname( __FILE__ ) );

/**
 * Class LP_Addon_Prerequisites
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class LP_Addon_Prerequisites {
	/**
	 * @var bool
	 */
	protected static $_instance = false;

	function __construct() {
		add_filter( 'learn_press_course_settings_meta_box_args', array( $this, 'admin_meta_box' ), 11 );
		add_action( 'learn_press_content_landing_summary', array( $this, 'enroll_notice' ), 34 );
		add_filter( 'learn_press_user_can_enroll_course', array( $this, 'can_enroll' ), 99, 3 );
		add_action( 'plugins_loaded', array( __CLASS__, 'load_text_domain' ) );
	}

	function can_enroll( $enrollable, $user, $course_id ) {
		$courses = $this->get_prerequisite_courses( $course_id );
		if ( $courses ) foreach ( $courses as $cid ) {
			if ( !$user->has( 'finished-course', $cid ) ) {
				add_filter( 'learn_press_user_can_not_enroll_course_message', array( $this, 'enroll_message' ), 99, 3 );
				return false;
			}
		}
		return $enrollable;
	}

	function enroll_message( $message, $course, $user ) {
		return false;
	}

	function get_prerequisite_courses( $course_id ) {
		$course = LP_Course::get_course( $course_id );
		if ( $course ) {
			return (array) $course->__get( 'course_prerequisite', false );
		}
		return false;
	}

	function enroll_notice() {
		global $post;
		$has_prerequisite = false;
		$courses          = $this->get_prerequisite_courses( $post->ID );
		if ( $courses ) foreach ( $courses as $cid ) {
			if ( !LP()->user->has( 'finished-course', $cid ) ) {
				$has_prerequisite = true;
				break;
			}
		}
		if ( !$has_prerequisite ) {
			return;
		}
		$message = __( 'NOTE: You have to finish these courses before you can enroll this course', 'learn_press' );
		if ( $courses = $this->get_prerequisite_courses( $post->ID ) ) {
			$message2 = array();
			foreach ( $courses as $course_id ) {
				if ( !LP()->user->has( 'finished-course', $course_id ) ) {
					$message2[] = get_the_title( $course_id );
				}
			}
			if ( $message2 ) {
				$message2 = sprintf( '<ul><li>%s</li></ul>', join( '</li><li>', $message2 ) );
				$message .= $message2;
			}
		}
		learn_press_display_message( $message, 'error' );
	}

	/**
	 * @param $meta_boxes
	 *
	 * @return mixed
	 */
	function admin_meta_box( $meta_boxes ) {
		$prerequisite = array(
			'name'        => __( 'Prerequisite Courses', 'learnpress-prerequisites' ),
			'id'          => "_lp_course_prerequisite",
			'type'        => 'select_advanced',
			'multiple'    => true,
			'description' => 'Course you have to finish before you can enroll to this course',
			'placeholder' => __( 'Course Prerequisite', 'learnpress-prerequisites' ),
			'std'         => '',
			'options'     => array()
		);

		global $wpdb;

		$post_id = !empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : 0;
		if ( $post_id ) {
			$post_author = get_post_field( 'post_author', $post_id );
		} else {
			$post_author = get_current_user_id();
		}

		settype( $post_id, 'array' );

		$query = $wpdb->prepare( "
			SELECT ID, post_title
			FROM {$wpdb->posts}
			WHERE post_type = %s AND post_author = %d AND post_status = %s
			AND ID NOT IN(" . join( ', ', $post_id ) . ")
		", 'lp_course', $post_author, 'publish' );
		if ( $options = $wpdb->get_results( $query ) ) {
			foreach ( $options as $o ) {
				$prerequisite['options'][$o->ID] = $o->post_title;
			}
		}
		array_unshift( $meta_boxes['fields'], $prerequisite );

		return $meta_boxes;
	}

	/**
	 * @return bool|LP_Addon_Prerequisites
	 */
	static function instance() {
		if ( !self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	static function load_text_domain() {
		if( function_exists('learn_press_load_plugin_text_domain')){ learn_press_load_plugin_text_domain(LP_PREREQUISITES_PLUGIN_PATH, true ); }
	}
}

add_action( 'learn_press_ready', array( 'LP_Addon_Prerequisites', 'instance' ) );

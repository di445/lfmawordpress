<?php
/**
 * Created by PhpStorm.
 * User: foobla
 * Date: 4/3/2015
 * Time: 10:49 AM
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

die();
/**
 * Add meta box prerequisite courses
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function learn_press_prerequisite_add_on( $meta_boxes ) {
	$prerequisite = array(
		'name'        => __( 'Prerequisite Courses', 'learnpress_prerequisites' ),
		'id'          => "_lpr_course_prerequisite",
		'type'        => 'post',
		'post_type'   => 'lpr_course',
		'field_type'  => 'select_advanced',
		'multiple'    => true,
		'description' => 'Course you have to finish before you can enroll to this course',
		'placeholder' => __( 'Course Prerequisite', 'learnpress_prerequisites' ),
		'std'         => ''
	);

	$prerequisite = array(
		'name'        => __( 'Prerequisite Courses', 'learnpress_prerequisites' ),
		'id'          => "_lpr_course_prerequisite",
		'type'        => 'select_advanced',
		'multiple'    => true,
		'description' => 'Course you have to finish before you can enroll to this course',
		'placeholder' => __( 'Course Prerequisite', 'learnpress_prerequisites' ),
		'std'         => '',
		'options' => array()
	);

	global $wpdb;

	$post_id = !empty($_REQUEST['post']) ? $_REQUEST['post'] : 0;
	if( $post_id ){
		$post_author = get_post_field( 'post_author', $post_id );
	}else{
		$post_author = get_current_user_id();
	}

	$query   = $wpdb->prepare("
		SELECT ID, post_title
		FROM {$wpdb->posts}
		WHERE post_type = %s AND post_author = %d AND post_status = %s
		AND ID NOT IN(" . $post_id . ")
	", 'lpr_course', $post_author, 'publish');
	if( $options = $wpdb->get_results( $query )){
		foreach( $options as $o ){
			$prerequisite['options'][$o->ID] = $o->post_title;
		}
	}
	array_unshift( $meta_boxes['fields'], $prerequisite );

	return $meta_boxes;
}

add_filter( 'learn_press_course_settings_meta_box_args', 'learn_press_prerequisite_add_on', 11 );

/**
 * @param bool
 * @param int
 * @param int
 * @param string
 *
 * @return bool
 */
function check_prerequisite_course( $allow_take_course, $user_id, $course_id, $payment_method ) {

	$prerequisite      = get_post_meta( $course_id, '_lpr_course_prerequisite', false );
	$require_courses   = array();

	if ( $prerequisite ) {
		if( $course_completed = (array)get_user_meta( $user_id, '_lpr_course_completed', true ) ){
			foreach ( $prerequisite as $the_course ) {
				if ( !array_key_exists( $the_course, $course_completed ) ) {
					$require_courses[] = $the_course;
					$allow_take_course = false;
				}
			}
		}else{
			$require_courses = $prerequisite;
		}
	}

	if ( !$allow_take_course ) {
		wp_dequeue_script( 'lpr-learnpress-js' );
		wp_enqueue_script( 'lpr-learnpress-prerequisite-js', untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/prerequisite.js', array( 'jquery' ) );
		ob_start();
		echo "<h3> You have to finish all the courses below before you can join this course: </h4>";
		echo "<ul>";
		foreach ( $require_courses as $require_course ) {
			echo '<li><a href="' . get_the_permalink( $require_course ) . '" >' . get_the_title( $require_course ) . '</a></li>';
		}
		echo "</ul>";
		$message = ob_get_clean();
		learn_press_add_message( 'error', $message );
	}
	return $allow_take_course;
}

add_action( 'learn_press_before_take_course', 'check_prerequisite_course', 10, 4 );

/**
 * Dequeue old javascript file
 */
function learn_press_prerequisite_dequeue_script() {
	wp_dequeue_script( 'lpr-learnpress-js' );
}

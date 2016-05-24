<?php 
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$course_id = get_the_ID();
$course_rate = learn_press_get_course_rate( $course_id );
$total = learn_press_get_course_rate_total( $course_id );
?>
<div class="course-rate">
<?php
    learn_press_course_review_template( 'rating-stars.php', array( 'rated' => $course_rate ) );
    $text=sprintf( _n('%d rating', '%d ratings', $total, 'learnpress-course-review'), $total);
?>
    <p class="review-number">
        <?php do_action( 'learn_press_before_total_review_number' );?>
        <?php echo $text ; ?>
        <?php do_action( 'learn_press_after_total_review_number' );?>
    </p>
</div>
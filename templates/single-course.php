<?php
use Politeia\Academia\Core\Helpers\Access;

get_header();
$course_id = get_the_ID();
if ( Access::has_access( get_current_user_id(), $course_id ) ) {
    the_post();
    ?>
    <div class="polilms-course">
        <h1><?php the_title(); ?></h1>
        <div class="course-content"><?php the_content(); ?></div>
        <h2><?php _e( 'Lessons', 'politeia-academia' ); ?></h2>
        <ol>
            <?php
            $lessons = get_posts( [
                'post_type' => 'polilms_lesson',
                'numberposts' => -1,
                'meta_key' => '_polilms_course_id',
                'meta_value' => $course_id,
                'orderby' => 'meta_value_num',
                'meta_key' => '_polilms_lesson_order',
                'order' => 'ASC'
            ] );
            foreach ( $lessons as $lesson ) {
                echo '<li><a href="' . get_permalink( $lesson ) . '">' . esc_html( $lesson->post_title ) . '</a></li>';
            }
            ?>
        </ol>
    </div>
    <?php
} else {
    polilms_render_gate( $course_id );
}
get_footer();

<?php
/**
 * Template for displaying a single lesson.
 *
 * @package Politeia\\Academia
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Politeia\Academia\Core\Helpers\Access;

get_header();

$course_id = intval( get_post_meta( get_the_ID(), '_polilms_course_id', true ) );
if ( Access::has_access( get_current_user_id(), $course_id ) ) :
    ?>
    <div class="polilms-single-lesson">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
    <?php
else :
    polilms_render_gate( $course_id );
endif;

get_footer();

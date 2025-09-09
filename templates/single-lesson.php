<?php
/**
 * Template for displaying a single lesson.
 *
 * @package Politeia\\Academia
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
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
get_footer();

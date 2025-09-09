<?php
/**
 * Template for displaying a quiz.
 *
 * @package Politeia\\Academia
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="polilms-quiz">
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>
</div>

<?php
get_footer();

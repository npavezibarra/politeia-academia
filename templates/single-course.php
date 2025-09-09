<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Politeia\Academia\Core\Helpers\Access;
?>
<!-- HEADER -->
<?php
// Load the default header template part.
include plugin_dir_path( __FILE__ ) . 'template-parts/header.php';
echo do_blocks( '<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->' );

$course_id = get_the_ID();
?>
<div id="primary" class="content-area">
<main id="polilms-single-course" class="site-main polilms-wrap">
<?php
if ( Access::has_access( get_current_user_id(), $course_id ) ) {
    the_post();
    ?>
    <header class="polilms-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>
    <div class="polilms-course-content"><?php the_content(); ?></div>
    <h2><?php _e( 'Lessons', 'politeia-academia' ); ?></h2>
    <ol>
        <?php
        $lessons = get_posts([
            'post_type'   => 'polilms_lesson',
            'numberposts' => -1,
            'meta_key'    => '_polilms_course_id',
            'meta_value'  => $course_id,
            'orderby'     => 'meta_value_num',
            'meta_key'    => '_polilms_lesson_order',
            'order'       => 'ASC',
        ]);
        foreach ( $lessons as $lesson ) {
            echo '<li><a href="' . get_permalink( $lesson ) . '">' . esc_html( $lesson->post_title ) . '</a></li>';
        }
        ?>
    </ol>
    <?php
} else {
    polilms_render_gate( $course_id );
}
?>
</main>
</div>
<?php
// Load the default footer template part.
echo do_blocks( '<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->' );
include plugin_dir_path( __FILE__ ) . 'template-parts/footer.php';
?>

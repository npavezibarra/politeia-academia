<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the default header template part so block themes render nav and styles.
include plugin_dir_path( __FILE__ ) . 'template-parts/header.php';
echo do_blocks( '<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->' );

$paged = max( 1, get_query_var( 'paged', 1 ) );
$query = new WP_Query(
    [
        'post_type'      => 'course',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => $paged,
    ]
);
?>
<div id="primary" class="content-area">
<main id="polilms-courses-archive" class="site-main polilms-wrap bb-grid">
  <header class="polilms-header">
    <h1 class="entry-title"><?php esc_html_e( 'Courses', 'politeia-academia' ); ?></h1>
  </header>

  <?php if ( $query->have_posts() ) : ?>
    <div class="polilms-course-grid">
      <?php while ( $query->have_posts() ) : $query->the_post();
        $course_id  = get_the_ID();
        $visibility = get_post_meta( $course_id, '_polilms_visibility', true ) ?: 'open_registered';
        $product_id = (int) get_post_meta( $course_id, '_polilms_wc_product_id', true );
        $thumb      = get_the_post_thumbnail( $course_id, 'medium', [ 'class' => 'polilms-thumb' ] );
      ?>
      <article <?php post_class( 'polilms-course-card' ); ?>>
        <a class="polilms-card-media" href="<?php the_permalink(); ?>"><?php echo $thumb ? : ''; ?></a>
        <div class="polilms-card-body">
          <h2 class="polilms-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <div class="polilms-card-excerpt"><?php the_excerpt(); ?></div>
          <div class="polilms-card-cta">
            <?php if ( $visibility === 'closed_paid' && $product_id ) : ?>
              <a class="button" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
                <?php esc_html_e( 'Buy course', 'politeia-academia' ); ?>
              </a>
            <?php else : ?>
              <a class="button" href="<?php the_permalink(); ?>">
                <?php esc_html_e( 'View course', 'politeia-academia' ); ?>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <nav class="polilms-pagination">
      <?php
        echo paginate_links(
            [
                'total'   => $query->max_num_pages,
                'current' => $paged,
            ]
        );
      ?>
    </nav>
  <?php else : ?>
    <p><?php esc_html_e( 'No courses found.', 'politeia-academia' ); ?></p>
  <?php endif; ?>
</main>
</div>
<?php
// Load the default footer template part.
echo do_blocks( '<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->' );
include plugin_dir_path( __FILE__ ) . 'template-parts/footer.php';
?>

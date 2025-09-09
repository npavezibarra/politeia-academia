<?php
if (!defined('ABSPATH')) exit;
get_header();

$paged = max(1, get_query_var('paged', 1));
$query = new WP_Query([
  'post_type'      => 'course',
  'post_status'    => 'publish',
  'posts_per_page' => 12,
  'paged'          => $paged,
]);

?>
<main id="polilms-courses-archive" class="polilms-wrap bb-grid">
  <header class="polilms-header">
    <h1 class="entry-title"><?php esc_html_e('Courses','politeia-academia'); ?></h1>
  </header>

  <?php if ($query->have_posts()): ?>
    <div class="polilms-course-grid">
      <?php while ($query->have_posts()): $query->the_post();
        $course_id = get_the_ID();
        $visibility = get_post_meta($course_id, '_polilms_visibility', true) ?: 'open_registered';
        $product_id = (int) get_post_meta($course_id, '_polilms_wc_product_id', true);
        $thumb = get_the_post_thumbnail($course_id, 'medium', ['class'=>'polilms-thumb']);
      ?>
      <article <?php post_class('polilms-course-card'); ?>>
        <a class="polilms-card-media" href="<?php the_permalink(); ?>"><?php echo $thumb ?: ''; ?></a>
        <div class="polilms-card-body">
          <h2 class="polilms-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <div class="polilms-card-excerpt"><?php the_excerpt(); ?></div>
          <div class="polilms-card-cta">
            <?php if ($visibility === 'closed_paid' && $product_id): ?>
              <a class="button" href="<?php echo esc_url(get_permalink($product_id)); ?>">
                <?php esc_html_e('Buy course','politeia-academia'); ?>
              </a>
            <?php else: ?>
              <a class="button" href="<?php the_permalink(); ?>">
                <?php esc_html_e('View course','politeia-academia'); ?>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <nav class="polilms-pagination">
      <?php
        echo paginate_links([
          'total'   => $query->max_num_pages,
          'current' => $paged,
        ]);
      ?>
    </nav>
  <?php else: ?>
    <p><?php esc_html_e('No courses found.','politeia-academia'); ?></p>
  <?php endif; ?>
</main>

<style>
/* minimal, theme-friendly */
.polilms-course-grid{display:grid;gap:1.25rem;grid-template-columns:repeat(auto-fill,minmax(260px,1fr))}
.polilms-course-card{background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:12px;overflow:hidden;display:flex;flex-direction:column}
.polilms-card-media img{width:100%;height:auto;display:block}
.polilms-card-body{padding:14px;display:flex;flex-direction:column;gap:.5rem}
.polilms-card-title{font-size:1.1rem;margin:0}
.polilms-card-excerpt{color:#555}
.polilms-card-cta .button{display:inline-block}
</style>
<?php get_footer(); ?>


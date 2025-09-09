<?php
namespace Politeia\Academia\Modules\Templates {

use Politeia\Academia\Core\Contracts\Module as ModuleContract;
use Politeia\Academia\Core\ServiceContainer;

class Module implements ModuleContract {
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function register(): void {
        add_filter( 'single_template', [ $this, 'single_templates' ] );
        add_shortcode( 'polilms_course_list', [ $this, 'course_list_shortcode' ] );
        add_shortcode( 'polilms_lesson_list', [ $this, 'lesson_list_shortcode' ] );
    }

    public function migrations(): array {
        return [];
    }

    public function single_templates( $template ) {
        if ( is_singular( 'polilms_lesson' ) ) {
            return $this->locate( 'single-lesson.php' );
        }
        if ( is_singular( 'polilms_course' ) ) {
            return $this->locate( 'single-course.php' );
        }
        return $template;
    }

    protected function locate( string $file ): string {
        $theme = locate_template( 'politeia-academia/' . $file );
        if ( $theme ) {
            return $theme;
        }
        return POLIAC_DIR . 'templates/' . $file;
    }

    public function course_list_shortcode( $atts ): string {
        $courses = get_posts( [ 'post_type' => 'polilms_course', 'numberposts' => -1 ] );
        if ( empty( $courses ) ) {
            return '';
        }
        ob_start();
        echo '<div class="polilms-course-list">';
        foreach ( $courses as $course ) {
            $link = get_permalink( $course );
            echo '<div class="course-item"><a href="' . esc_url( $link ) . '">' . esc_html( $course->post_title ) . '</a></div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    public function lesson_list_shortcode( $atts ): string {
        $atts = shortcode_atts( [ 'course_id' => 0 ], $atts );
        $course_id = intval( $atts['course_id'] );
        if ( ! $course_id ) {
            return '';
        }
        $lessons = get_posts( [
            'post_type' => 'polilms_lesson',
            'numberposts' => -1,
            'meta_key' => '_polilms_course_id',
            'meta_value' => $course_id,
            'orderby' => 'meta_value_num',
            'meta_key' => '_polilms_lesson_order',
            'order' => 'ASC'
        ] );
        ob_start();
        echo '<ol class="polilms-lesson-list">';
        foreach ( $lessons as $lesson ) {
            $link = get_permalink( $lesson );
            echo '<li><a href="' . esc_url( $link ) . '">' . esc_html( $lesson->post_title ) . '</a></li>';
        }
        echo '</ol>';
        return ob_get_clean();
    }
}
}

namespace {
    function polilms_render_gate( int $course_id ): void {
        $template = locate_template( 'politeia-academia/parts/gate.php', false, false );
        if ( ! $template ) {
            $template = POLIAC_DIR . 'templates/parts/gate.php';
        }
        $course_id = intval( $course_id );
        include $template;
    }
}

<?php
namespace Politeia\Academia\Modules\REST;

use Politeia\Academia\Core\Contracts\Module as ModuleContract;
use Politeia\Academia\Core\ServiceContainer;
use Politeia\Academia\Core\Helpers\Access;
use Politeia\Academia\Modules\Enrollment\Module as Enrollment;

class Module implements ModuleContract {
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function register(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function migrations(): array {
        return [];
    }

    public function register_routes(): void {
        register_rest_route( 'polilms/v1', '/courses', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_courses' ],
        ] );
        register_rest_route( 'polilms/v1', '/courses/(?P<id>\d+)/lessons', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_course_lessons' ],
            'args' => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ] );
        register_rest_route( 'polilms/v1', '/progress/(?P<course_id>\d+)', [
            'methods' => 'GET',
            'permission_callback' => function() { return is_user_logged_in(); },
            'callback' => [ $this, 'get_progress' ],
            'args' => [ 'course_id' => [ 'validate_callback' => 'is_numeric' ] ],
        ] );
        register_rest_route( 'polilms/v1', '/progress/complete', [
            'methods' => 'POST',
            'permission_callback' => function() { return is_user_logged_in() && wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'] ?? '', 'wp_rest' ); },
            'callback' => [ $this, 'post_progress_complete' ],
        ] );
        register_rest_route( 'polilms/v1', '/quiz/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_quiz' ],
            'args' => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ] );
        register_rest_route( 'polilms/v1', '/quiz/submit', [
            'methods' => 'POST',
            'permission_callback' => function() { return is_user_logged_in() && wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'] ?? '', 'wp_rest' ); },
            'callback' => [ $this, 'post_quiz_submit' ],
        ] );
    }

    public function get_courses( $request ) {
        $courses = get_posts( [ 'post_type' => 'course', 'numberposts' => -1 ] );
        $data = [];
        foreach ( $courses as $course ) {
            $lessons = get_posts( [ 'post_type' => 'polilms_lesson', 'numberposts' => -1, 'meta_key' => '_polilms_course_id', 'meta_value' => $course->ID ] );
            $data[] = [
                'id' => $course->ID,
                'title' => $course->post_title,
                'excerpt' => wp_strip_all_tags( $course->post_excerpt ),
                'visibility' => Access::course_visibility( $course->ID ),
                'thumbnail' => get_the_post_thumbnail_url( $course->ID, 'thumbnail' ),
                'lessons_count' => count( $lessons ),
            ];
        }
        return rest_ensure_response( $data );
    }

    public function get_course_lessons( $request ) {
        $course_id = intval( $request['id'] );
        $lessons = get_posts( [
            'post_type' => 'polilms_lesson',
            'numberposts' => -1,
            'meta_key' => '_polilms_course_id',
            'meta_value' => $course_id,
            'orderby' => 'meta_value_num',
            'meta_key' => '_polilms_lesson_order',
            'order' => 'ASC'
        ] );
        $completed = [];
        if ( is_user_logged_in() ) {
            $completed = $this->get_progress_ids( get_current_user_id(), $course_id );
        }
        $data = [];
        foreach ( $lessons as $lesson ) {
            $data[] = [
                'id' => $lesson->ID,
                'title' => $lesson->post_title,
                'order' => intval( get_post_meta( $lesson->ID, '_polilms_lesson_order', true ) ),
                'completed' => in_array( $lesson->ID, $completed, true ),
            ];
        }
        return rest_ensure_response( $data );
    }

    protected function get_progress_ids( int $user_id, int $course_id ): array {
        global $wpdb;
        $table = \Politeia\Academia\Core\Helpers\DB::table( 'progress' );
        return $wpdb->get_col( $wpdb->prepare( "SELECT lesson_id FROM {$table} WHERE user_id = %d AND course_id = %d", $user_id, $course_id ) );
    }

    public function get_progress( $request ) {
        $user_id = get_current_user_id();
        $course_id = intval( $request['course_id'] );
        return rest_ensure_response( $this->get_progress_ids( $user_id, $course_id ) );
    }

    public function post_progress_complete( $request ) {
        $lesson_id = intval( $request['lesson_id'] );
        $course_id = intval( get_post_meta( $lesson_id, '_polilms_course_id', true ) );
        $user_id = get_current_user_id();
        $success = Enrollment::progress_mark_complete( $user_id, $course_id, $lesson_id );
        return rest_ensure_response( [ 'success' => $success ] );
    }

    public function get_quiz( $request ) {
        $quiz_id = intval( $request['id'] );
        global $wpdb;
        $table = \Politeia\Academia\Core\Helpers\DB::table( 'quizzes' );
        $quiz = $wpdb->get_row( $wpdb->prepare( "SELECT id, lesson_id, title FROM {$table} WHERE id = %d", $quiz_id ), ARRAY_A );
        if ( ! $quiz ) {
            return new \WP_Error( 'not_found', __( 'Quiz not found', 'politeia-academia' ), [ 'status' => 404 ] );
        }
        // Questions would be loaded here; omitted for brevity.
        return rest_ensure_response( $quiz );
    }

    public function post_quiz_submit( $request ) {
        $quiz_id = intval( $request['quiz_id'] );
        $answers = $request['answers'];
        $user_id = get_current_user_id();
        global $wpdb;
        $table = \Politeia\Academia\Core\Helpers\DB::table( 'quiz_attempts' );
        $score = 0; // grading logic omitted
        $passed = 0;
        $wpdb->query( $wpdb->prepare( "INSERT INTO {$table} (quiz_id, user_id, score, passed, answers, created_at) VALUES (%d, %d, %f, %d, %s, NOW())", $quiz_id, $user_id, $score, $passed, maybe_serialize( $answers ) ) );
        return rest_ensure_response( [ 'score' => $score, 'passed' => (bool) $passed ] );
    }
}

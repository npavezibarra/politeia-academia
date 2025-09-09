<?php
namespace Politeia\Academia\Modules\BuddyBoss;

use Politeia\Academia\Core\Contracts\Module as ModuleContract;
use Politeia\Academia\Core\ServiceContainer;

class Module implements ModuleContract {
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function register(): void {
        if ( ! function_exists( 'bp_activity_add' ) ) {
            return;
        }
        add_action( 'polilms_course_enrolled', [ $this, 'activity_enrolled' ], 10, 4 );
        add_action( 'polilms_lesson_completed', [ $this, 'activity_completed' ], 10, 3 );
    }

    public function migrations(): array {
        return [];
    }

    protected function is_enabled(): bool {
        $settings = get_option( 'polilms_settings', [] );
        return $settings['enable_buddyboss_activity'] ?? true;
    }

    public function activity_enrolled( int $user_id, int $course_id, string $source, ?string $ref ): void {
        if ( ! $this->is_enabled() ) {
            return;
        }
        $course = get_post( $course_id );
        if ( ! $course ) {
            return;
        }
        bp_activity_add( [
            'user_id' => $user_id,
            'component' => 'courses',
            'type' => 'course_enrolled',
            'item_id' => $course_id,
            'content' => sprintf( __( 'Enrolled in %s', 'politeia-academia' ), $course->post_title ),
        ] );
    }

    public function activity_completed( int $user_id, int $course_id, int $lesson_id ): void {
        if ( ! $this->is_enabled() ) {
            return;
        }
        $lesson = get_post( $lesson_id );
        if ( ! $lesson ) {
            return;
        }
        bp_activity_add( [
            'user_id' => $user_id,
            'component' => 'lessons',
            'type' => 'lesson_completed',
            'item_id' => $lesson_id,
            'content' => sprintf( __( 'Completed lesson %s', 'politeia-academia' ), $lesson->post_title ),
        ] );
    }
}

<?php
namespace Politeia\Academia\Modules\Enrollment;

use Politeia\Academia\Core\Contracts\Module as ModuleContract;
use Politeia\Academia\Core\ServiceContainer;
use Politeia\Academia\Core\Helpers\DB;

class Module implements ModuleContract {
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function register(): void {
        // Enrollment module currently provides static APIs only.
    }

    public function migrations(): array {
        return [ Migrations\EnrollmentsProgress::class ];
    }

    public static function enroll_user(int $user_id, int $course_id, string $source = 'manual', ?string $ref = null): bool {
        global $wpdb;
        $table = DB::table( 'enrollments' );
        $result = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table} (user_id, course_id, status, source, ref, created_at) VALUES ( %d, %d, 'active', %s, %s, NOW() ) ON DUPLICATE KEY UPDATE status = 'active', source = VALUES(source), ref = VALUES(ref)", $user_id, $course_id, $source, $ref ) );
        if ( false !== $result ) {
            do_action( 'polilms_course_enrolled', $user_id, $course_id, $source, $ref );
            return true;
        }
        return false;
    }

    public static function revoke_enrollment(int $user_id, int $course_id): bool {
        global $wpdb;
        $table = DB::table( 'enrollments' );
        $result = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET status = 'revoked' WHERE user_id = %d AND course_id = %d", $user_id, $course_id ) );
        return $result !== false;
    }

    public static function progress_mark_complete(int $user_id, int $course_id, int $lesson_id): bool {
        global $wpdb;
        $table = DB::table( 'progress' );
        $result = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$table} (user_id, course_id, lesson_id, completed_at) VALUES ( %d, %d, %d, NOW() )", $user_id, $course_id, $lesson_id ) );
        if ( false !== $result ) {
            do_action( 'polilms_lesson_completed', $user_id, $course_id, $lesson_id );
            return true;
        }
        return false;
    }
}

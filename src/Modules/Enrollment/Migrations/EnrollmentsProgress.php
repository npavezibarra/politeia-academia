<?php
namespace Politeia\Academia\Modules\Enrollment\Migrations;

use Politeia\Academia\Core\Contracts\Migration;
use Politeia\Academia\Core\Helpers\DB;

class EnrollmentsProgress implements Migration {
    public static function version(): string {
        return '2025_09_09_000003';
    }

    public function up(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        $enrollments = DB::table( 'enrollments' );
        $sql = "CREATE TABLE {$enrollments} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            course_id BIGINT UNSIGNED NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            source VARCHAR(50) NULL,
            ref VARCHAR(100) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_course (user_id, course_id)
        ) {$charset};";
        dbDelta( $sql );

        $progress = DB::table( 'progress' );
        $sql2 = "CREATE TABLE {$progress} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            course_id BIGINT UNSIGNED NOT NULL,
            lesson_id BIGINT UNSIGNED NOT NULL,
            completed_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_lesson (user_id, lesson_id)
        ) {$charset};";
        dbDelta( $sql2 );
    }
}

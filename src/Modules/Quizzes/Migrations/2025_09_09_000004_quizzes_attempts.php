<?php
namespace Politeia\Academia\Modules\Quizzes\Migrations;

use Politeia\Academia\Core\Contracts\Migration;
use Politeia\Academia\Core\Helpers\DB;

class Init_2025_09_09_000004_quizzes_attempts implements Migration {
    public function up(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();

        $quizzes = DB::table( 'quizzes' );
        $sql = "CREATE TABLE {$quizzes} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            lesson_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) {$charset};";
        dbDelta( $sql );

        $attempts = DB::table( 'quiz_attempts' );
        $sql2 = "CREATE TABLE {$attempts} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            quiz_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            score FLOAT NOT NULL,
            passed TINYINT(1) NOT NULL DEFAULT 0,
            answers LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) {$charset};";
        dbDelta( $sql2 );
    }
}

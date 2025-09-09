<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$settings = get_option( 'polilms_settings', [] );
if ( empty( $settings['purge_on_uninstall'] ) ) {
    return;
}

global $wpdb;
$tables = [ 'enrollments', 'progress', 'quizzes', 'quiz_attempts' ];
foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS " . POLIAC_TABLE_PREFIX . $table );
}

delete_option( 'polilms_settings' );
delete_option( POLIAC_DB_VERSION_OPTION );

delete_post_meta_by_key( '_polilms_visibility' );
delete_post_meta_by_key( '_polilms_wc_product_id' );
delete_post_meta_by_key( '_polilms_course_id' );
delete_post_meta_by_key( '_polilms_lesson_order' );
delete_post_meta_by_key( '_polilms_required' );

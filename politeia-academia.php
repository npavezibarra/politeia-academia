<?php
/**
 * Plugin Name:       Politeia Academia (LMS)
 * Description:       Lean LMS (Courses, Lessons, Quizzes) with WooCommerce + BuddyBoss integration.
 * Version:           0.1.0
 * Author:            Politeia
 * Text Domain:       politeia-academia
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'POLIAC_FILE', __FILE__ );
define( 'POLIAC_DIR', plugin_dir_path( __FILE__ ) );
define( 'POLIAC_URL', plugin_dir_url( __FILE__ ) );
define( 'POLIAC_DB_VERSION_OPTION', 'politeia_academia_db_version' ); // global db version
define( 'POLIAC_TABLE_PREFIX', $GLOBALS['wpdb']->prefix . 'politeia_lms_' ); // wp_ → wp_politeia_lms_*

// Composer
if ( file_exists( POLIAC_DIR . 'vendor/autoload.php' ) ) {
    require POLIAC_DIR . 'vendor/autoload.php';
}

register_activation_hook( __FILE__, function () {
    \Politeia\Academia\Core\Activator::activate();
} );

register_uninstall_hook( __FILE__, function () {
    // keep user data by default; provide setting to remove on uninstall
} );

add_action( 'plugins_loaded', function () {
    ( new \Politeia\Academia\Core\Plugin() )->boot();
} );


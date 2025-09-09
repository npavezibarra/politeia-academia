<?php
namespace Politeia\Academia\Core;

use Politeia\Academia\Core\Contracts\Module;

class Plugin {
    protected ServiceContainer $container;

    public function __construct() {
        $this->container = new ServiceContainer();
    }

    public function boot(): void {
        if ( is_admin() ) {
            ( new \Politeia\Academia\Admin\SettingsPage() )->register();
        }

        $modules = apply_filters( 'polilms_modules', [
            \Politeia\Academia\Modules\Courses\Module::class,
            \Politeia\Academia\Modules\Lessons\Module::class,
            \Politeia\Academia\Modules\Enrollment\Module::class,
            \Politeia\Academia\Modules\Quizzes\Module::class,
            \Politeia\Academia\Modules\WooCommerce\Module::class,
            \Politeia\Academia\Modules\BuddyBoss\Module::class,
            \Politeia\Academia\Modules\REST\Module::class,
            \Politeia\Academia\Modules\Templates\Module::class,
        ] );

        foreach ( $modules as $module_class ) {
            if ( class_exists( $module_class ) ) {
                $module = new $module_class( $this->container );
                if ( $module instanceof Module ) {
                    $module->register();
                }
            }
        }

        add_action( 'init', function() {
            if ( get_option( 'polilms_needs_rewrite_flush' ) ) {
                flush_rewrite_rules( false );
                delete_option( 'polilms_needs_rewrite_flush' );
            }
        }, 20 );
    }

    public function container(): ServiceContainer {
        return $this->container;
    }
}

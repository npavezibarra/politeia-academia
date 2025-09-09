<?php
namespace Politeia\Academia\Core\Migrations;

use Politeia\Academia\Core\Contracts\Migration;
use Politeia\Academia\Core\Contracts\Module;
use Politeia\Academia\Core\ServiceContainer;

class MigrationRunner {
    public function run(): void {
        $container = new ServiceContainer();
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
                $module = new $module_class( $container );
                if ( $module instanceof Module ) {
                    foreach ( $module->migrations() as $migration_class ) {
                        if ( class_exists( $migration_class ) ) {
                            $migration = new $migration_class();
                            if ( $migration instanceof Migration ) {
                                $migration->up();
                            }
                        }
                    }
                }
            }
        }
    }
}

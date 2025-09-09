<?php
namespace Politeia\Academia\Core;

class Activator {
    public static function activate(): void {
        ( new Migrations\MigrationRunner() )->run();
        if ( ! get_option( 'polilms_needs_rewrite_flush' ) ) {
            add_option( 'polilms_needs_rewrite_flush', 1 );
        }
    }
}

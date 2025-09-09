<?php
namespace Politeia\Academia\Core;

class Activator {
    public static function activate(): void {
        ( new Migrations\MigrationRunner() )->run();
    }
}

<?php
namespace Politeia\Academia\Modules\Courses\Migrations;

use Politeia\Academia\Core\Contracts\Migration;

class Init implements Migration {
    public static function version(): string {
        return '2025_09_09_000001';
    }

    public function up(): void {
        // Courses use custom post types; no tables required.
    }
}

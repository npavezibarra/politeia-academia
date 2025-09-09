<?php
namespace Politeia\Academia\Modules\Lessons\Migrations;

use Politeia\Academia\Core\Contracts\Migration;

class Init implements Migration {
    public static function version(): string {
        return '2025_09_09_000002';
    }

    public function up(): void {
        // Lessons use custom post types; no tables required.
    }
}

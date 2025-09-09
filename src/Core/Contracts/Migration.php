<?php
namespace Politeia\Academia\Core\Contracts;

interface Migration {
    public static function version(): string;
    public function up(): void;
}

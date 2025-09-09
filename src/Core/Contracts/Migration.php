<?php
namespace Politeia\Academia\Core\Contracts;

interface Migration {
    public function up(): void;
}

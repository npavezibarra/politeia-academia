<?php
namespace Politeia\Academia\Core\Contracts;

interface Module {
    public function register(): void;
    public function migrations(): array;
}

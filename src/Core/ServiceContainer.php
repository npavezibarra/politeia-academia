<?php
namespace Politeia\Academia\Core;

class ServiceContainer {
    protected array $services = [];

    public function set(string $id, $service): void {
        $this->services[$id] = $service;
    }

    public function get(string $id) {
        return $this->services[$id] ?? null;
    }
}

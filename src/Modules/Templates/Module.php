<?php
namespace Politeia\Academia\Modules\Templates;

use Politeia\Academia\Core\Contracts\Module as ModuleContract;

class Module implements ModuleContract {
  public function register(): void {
    // Block themes rely on HTML templates placed in the active theme.
  }

  public function migrations(): array {
    return [];
  }
}


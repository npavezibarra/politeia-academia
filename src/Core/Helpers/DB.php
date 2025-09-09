<?php
namespace Politeia\Academia\Core\Helpers;

class DB {
    public static function table(string $suffix): string {
        return POLIAC_TABLE_PREFIX . $suffix;
    }
}

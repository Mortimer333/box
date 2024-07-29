<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

interface DatabaseManagerInterface
{
    public function persist(): void;

    public function rollback(): void;

    public function hasActiveTransaction(): bool;

    public function beginTransaction(): void;

    public function reconnectIfNecessary(): void;

    public function clear(): void;
}

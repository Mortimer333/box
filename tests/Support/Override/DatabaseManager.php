<?php

declare(strict_types=1);

namespace App\Tests\Support\Override;

use App\Application\Port\Secondary\DatabaseManagerInterface;

class DatabaseManager implements DatabaseManagerInterface
{
    public static ?DatabaseManagerInterface $mock = null;

    public function __construct(
        protected DatabaseManagerInterface $default,
    ) {
    }

    public function getManager(): DatabaseManagerInterface
    {
        return self::$mock ?? $this->default;
    }

    public function persist(): void
    {
        $this->getManager()->persist();
    }

    public function rollback(): void
    {
        $this->getManager()->rollback();
    }

    public function hasActiveTransaction(): bool
    {
        return $this->getManager()->hasActiveTransaction();
    }

    public function beginTransaction(): void
    {
        $this->getManager()->beginTransaction();
    }

    public function reconnectIfNecessary(): void
    {
        $this->getManager()->reconnectIfNecessary();
    }
}

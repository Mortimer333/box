<?php

declare(strict_types=1);

namespace App\Adapter\Secondary;

use App\Application\Port\Secondary\DatabaseManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @codeCoverageIgnore Removing him from tests as this is basically proxy class.
 */
final readonly class DatabaseManager implements DatabaseManagerInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected ManagerRegistry $managerRegistry,
    ) {
    }

    public function persist(): void
    {
        $this->em->flush();
    }

    public function rollback(): void
    {
        $this->em->rollback();
    }

    public function hasActiveTransaction(): bool
    {
        return $this->em->getConnection()->isTransactionActive();
    }

    public function beginTransaction(): void
    {
        $this->em->beginTransaction();
    }

    public function reconnectIfNecessary(): void
    {
        if (!$this->em->isOpen()) {
            $this->managerRegistry->resetManager();
        }
    }
}

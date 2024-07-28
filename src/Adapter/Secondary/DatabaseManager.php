<?php

declare(strict_types=1);

namespace App\Adapter\Secondary;

use App\Application\Port\Secondary\DatabaseManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DatabaseManager implements DatabaseManagerInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function persist(): void
    {
        $this->em->flush();
    }
}

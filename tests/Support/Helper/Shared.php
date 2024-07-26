<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Entity\User;

class Shared extends \Codeception\Module
{
    protected Connection $connection;
    protected EntityManagerInterface $em;
    protected ManagerRegistry $managerRegistry;
    /** @var array<array<string, object|string>> $entities */
    protected array $entities = [];

    public function clearToRemove(): void
    {
        $this->entities = [];
    }

    public function addToRemove(object $entity): void
    {
        $this->entities[] = $entity;
    }

    public function unshiftToRemove(object $entity): void
    {
        array_unshift($this->entities, $entity);
    }

    /**
     * @template T
     *
     * @param class-string<T> $service
     *
     * @return T
     *
     * @throws \Exception
     */
    public function getService(string $service)
    {
        return $this->getModule('Symfony')->_getContainer()->get($service);
    }

    public function getConnection(): Connection
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        $this->connection = $this->getService(Connection::class);

        return $this->connection;
    }

    public function getEm(): EntityManagerInterface
    {
        if (isset($this->em)) {
            return $this->em;
        }

        $this->em = $this->getService(EntityManagerInterface::class);

        return $this->em;
    }

    public function getManagerRegistry(): ManagerRegistry
    {
        if (isset($this->managerRegistry)) {
            return $this->managerRegistry;
        }

        $this->managerRegistry = $this->getService(ManagerRegistry::class);

        return $this->managerRegistry;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function removeSavedEntities(): void
    {
        $em = $this->getEm();
        if (!$em->isOpen()) {
            $this->getManagerRegistry()->resetManager(); // Have to reset entity on exception
            $this->connection = $em->getConnection();
        }

        $transaction = $this->getConnection()->isTransactionActive();
        if ($transaction) {
            $this->getConnection()->commit();
        }

        $doctrine = $this->getManagerRegistry();
        foreach ($this->entities as $entity) {
            if (!$em->isOpen()) {
                $doctrine->resetManager();
            }

            if (!$entity->getId()) {
                continue;
            }

            $entity = $em->getRepository($entity::class)->find($entity->getId()); // @phpstan-ignore-line
            if (!$entity) {
                continue;
            }

            $em->remove($entity);
            if ($entity instanceof User && $entity->getData()) {
                $em->remove($entity->getData());
            }
        }
        $em->flush();

        if ($transaction) {
            $this->getConnection()->beginTransaction();
        }
    }
}

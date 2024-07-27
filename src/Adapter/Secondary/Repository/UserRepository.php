<?php

namespace App\Adapter\Secondary\Repository;

use App\Adapter\Secondary\Entity\User;
use App\Application\Infrastructure\Exception\UserNotFoundException;
use App\Application\Port\Secondary\UserInterface;
use App\Application\Port\Secondary\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function get(int $id): UserInterface
    {
        $user = $this->find($id);
        if (!$user) {
            throw new UserNotFoundException($id);
        }

        return $user;
    }
}

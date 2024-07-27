<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Application\Infrastructure\Exception\UserNotFoundException;

interface UserRepositoryInterface
{
    /**
     * @throws UserNotFoundException
     */
    public function get(int $id): UserInterface;
}

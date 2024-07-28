<?php

declare(strict_types=1);

namespace App\Tests\Unit\Adapter\Secondary\Repository;

use App\Application\Infrastructure\Exception\UserNotFoundException;
use App\Application\Port\Secondary\UserInterface;
use App\Application\Port\Secondary\UserRepositoryInterface;
use App\Tests\Unit\BaseUnitAbstract;

/**
 * @covers \App\Adapter\Secondary\Repository\UserRepository
 */
class UserRepositoryTest extends BaseUnitAbstract
{
    public function testRetrievesUserSuccessfully(): void
    {
        $repository = $this->tester->getService(UserRepositoryInterface::class);
        $user = $repository->get(1);
        $this->assertInstanceOf(UserInterface::class, $user);
    }

    public function testExceptionIsThrownWhenRetrievingNonExistingTransaction(): void
    {
        $repository = $this->tester->getService(UserRepositoryInterface::class);
        $this->expectException(UserNotFoundException::class);
        $repository->get(0);
    }
}

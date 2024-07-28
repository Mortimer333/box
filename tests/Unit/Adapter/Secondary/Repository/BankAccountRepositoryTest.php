<?php

declare(strict_types=1);

namespace App\Tests\Unit\Adapter\Secondary\Repository;

use App\Adapter\Secondary\DataFixtures\Test\BankAccountFixtures;
use App\Application\Infrastructure\Exception\BankAccountNotFoundException;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Tests\Unit\BaseUnitAbstract;
use Codeception\Attribute\Examples;
use Doctrine\ORM\OptimisticLockException;

/**
 * @covers \App\Adapter\Secondary\Repository\BankAccountRepository
 */
class BankAccountRepositoryTest extends BaseUnitAbstract
{
    public function testRetrieveBankAccount(): void
    {
        $bankAccountRepository = $this->tester->getService(BankAccountRepositoryInterface::class);
        $bankAccount = $bankAccountRepository->get(1);
        $this->tester->assertInstanceOf(BankAccountInterface::class, $bankAccount);
    }

    public function testExceptionIsThrownWhenRetrievingNonExistingBankAccount(): void
    {
        $bankAccountRepository = $this->tester->getService(BankAccountRepositoryInterface::class);
        $this->expectException(BankAccountNotFoundException::class);
        $bankAccountRepository->get(0);
    }

    #[Examples(BankAccountFixtures::ACCOUNT_NUMBER_ONE, true)]
    #[Examples('test', false)]
    public function testRetrieveBankAccountByIdentifier(string $identifier, bool $expected): void
    {
        $bankAccountRepository = $this->tester->getService(BankAccountRepositoryInterface::class);
        $bankAccount = $bankAccountRepository->getByIdentifier($identifier);
        $this->assertEquals($expected, $bankAccount instanceof BankAccountInterface);
    }

    public function testRetrieveBankAccountWithOptimisticLock(): void
    {
        $bankAccountRepository = $this->tester->getService(BankAccountRepositoryInterface::class);
        $bankAccount = $bankAccountRepository->lockOptimistic(1);
        $this->tester->assertInstanceOf(BankAccountInterface::class, $bankAccount);
    }

    public function testRetrieveBankAccountWithOptimisticLockButWrongVersion(): void
    {
        $bankAccountRepository = $this->tester->getService(BankAccountRepositoryInterface::class);
        $this->expectException(OptimisticLockException::class);
        $bankAccountRepository->lockOptimistic(1, 0);
    }

    public function testRetrieveBankAccountWithOptimisticLockAndSaveButVersionChanged(): void
    {
        $bankAccountRepository = $this->tester->getService(BankAccountRepositoryInterface::class);
        $databaseManager = $this->tester->getService(DatabaseManagerInterface::class);
        /** @var BankAccountInterface $bankAccount */
        $bankAccount = $bankAccountRepository->lockOptimistic(1);
        $bankAccount->setCredit(20);
        $bankAccount->setVersion(0);
        $this->expectException(OptimisticLockException::class);
        $databaseManager->persist();
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Infrastructure\Service\TransactionService;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\RetrieveTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\CurrencyEnum;
use App\Domain\Transfer;
use App\Tests\Unit\BaseUnitAbstract;
use Codeception\Stub\Expected;
use Doctrine\ORM\OptimisticLockException;
use Faker\Factory;
use Faker\Provider\Base as FakerBase;

/**
 * @covers \App\Application\Infrastructure\Service\TransactionService
 */
class TransactionServiceTest extends BaseUnitAbstract
{
    public function testTransactionRetryStrategy(): void
    {
        $generator = Factory::create();
        $userId = FakerBase::numberBetween(1, 5);
        $senderAccountId = FakerBase::numberBetween(6, 10);
        $receiverAccountIdentifier = $this->tester->generateAccountNumber();
        $title = $generator->title();
        $receiverName = $generator->name();
        $amount = FakerBase::numberBetween(11, 15);
        $address = $generator->address();

        $bankAccountRepositoryMock = $this->makeEmpty(BankAccountRepositoryInterface::class, [
            'lockOptimistic' => $this->makeEmpty(BankAccountInterface::class, [
                'getAccountNumber' => $receiverAccountIdentifier,
                'getCredit' => 50.0,
                'getReserved' => 0.0,
                'getId' => 1,
                'getCurrency' => CurrencyEnum::USD,
            ]),
        ]);

        $transactionChainRoot = $this->makeEmpty(TransactionChainLinkInterface::class, [
            'process' => function (Transfer $transaction, BankAccountInterface $bankAccount) use (
                $userId,
                $receiverAccountIdentifier,
                $title,
                $receiverName,
                $amount,
                $address,
            ) {
                $this->tester->assertEquals($userId, $transaction->sender->userId);
                $this->tester->assertEquals($receiverAccountIdentifier, $transaction->sender->bankAccountNumber);
                $this->tester->assertEquals($receiverAccountIdentifier, $transaction->receiver->bankAccountNumber);
                $this->tester->assertEquals($receiverName, $transaction->receiver->name);
                $this->tester->assertEquals($address, $transaction->receiver->address);
                $this->tester->assertEquals($title, $transaction->title);
                $this->tester->assertEquals($amount, $transaction->getAmount());

                throw new OptimisticLockException('Optimistic lock exception', 'TestEntity');
            },
        ]);

        $retrieveTransactionRepository = $this->makeEmpty(RetrieveTransactionRepositoryInterface::class, [
            'retrieveSumBetweenDateWithoutFailures' => 0,
        ]);

        $databaseManager = $this->tester->getService(DatabaseManagerInterface::class);
        $databaseManagerProxyMock = $this->makeEmpty(DatabaseManagerInterface::class, [
            'hasActiveTransaction' => Expected::exactly(3, function () use ($databaseManager): bool
            {
                return $databaseManager->hasActiveTransaction();
            }),
            'beginTransaction' => Expected::exactly(3, function () use ($databaseManager): void
            {
                $databaseManager->beginTransaction();

            }),
            'rollback' => Expected::exactly(3, function () use ($databaseManager): void
            {
                $databaseManager->rollback();
            }),
            'reconnectIfNecessary' => Expected::exactly(2, function () use ($databaseManager): void
            {
                $databaseManager->reconnectIfNecessary();
            }),
        ]);

        $transactionService = new TransactionService(
            $bankAccountRepositoryMock,
            $transactionChainRoot,
            $retrieveTransactionRepository,
            $databaseManagerProxyMock,
        );

        $this->expectException(OptimisticLockException::class);
        $transactionService->process(
            $userId,
            $senderAccountId,
            $receiverAccountIdentifier,
            $title,
            $receiverName,
            $amount,
            $address,
        );
    }
}

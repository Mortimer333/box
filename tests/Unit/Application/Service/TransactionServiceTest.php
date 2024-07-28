<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Infrastructure\Service\TransactionService;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\RetrieveTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\ConfigurationException;
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
    public function testExceptionIsThrownOnMissingConfig(): void
    {
        $transactionService = new TransactionService(
            $this->makeEmpty(BankAccountRepositoryInterface::class),
            $this->makeEmpty(TransactionChainLinkInterface::class),
            $this->makeEmpty(RetrieveTransactionRepositoryInterface::class),
            $this->makeEmpty(DatabaseManagerInterface::class),
        );

        $_ENV['MAX_PROCESS_RETRY_COUNT'] = null;
        $this->expectException(ConfigurationException::class);
        $transactionService->process(0,0,'','','',0.0);
    }

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
                'getAccountNumber' => Expected::exactly(3, $receiverAccountIdentifier),
                'getCredit' => Expected::exactly(3, 50.0),
                'getReserved' => Expected::exactly(3, 0.0),
                'getId' => Expected::exactly(3, 1),
                'getCurrency' => Expected::exactly(3, CurrencyEnum::USD),
            ]),
        ]);

        $transactionChainRoot = $this->makeEmpty(TransactionChainLinkInterface::class, [
            'process' => Expected::exactly(3, function (Transfer $transaction, BankAccountInterface $bankAccount) use (
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
                $this->tester->assertEquals($amount, $transaction->amount);

                throw new OptimisticLockException('Optimistic lock exception', 'TestEntity');
            }),
        ]);

        $retrieveTransactionRepository = $this->makeEmpty(RetrieveTransactionRepositoryInterface::class, [
            'retrieveSumBetweenDateWithoutFailures' => Expected::exactly(3, 0),
        ]);

        $databaseManager = $this->tester->getService(DatabaseManagerInterface::class);
        $databaseManagerProxyMock = $this->makeEmpty(DatabaseManagerInterface::class, [
            'hasActiveTransaction' => Expected::exactly(3, function () use ($databaseManager): bool {
                return $databaseManager->hasActiveTransaction();
            }),
            'beginTransaction' => Expected::exactly(3, function () use ($databaseManager): void {
                $databaseManager->beginTransaction();
            }),
            'rollback' => Expected::exactly(3, function () use ($databaseManager): void {
                $databaseManager->rollback();
            }),
            'reconnectIfNecessary' => Expected::exactly(2, function () use ($databaseManager): void {
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

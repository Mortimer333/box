<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Adapter\Secondary\Entity\BankAccount;
use App\Application\Infrastructure\Exception\TransactionNotFoundException;
use App\Application\Infrastructure\Message\TransferCommissionFeeFoundsMessage;
use App\Application\Infrastructure\Service\ExternalTransactionHandler;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\ExternalBankClientInterface;
use App\Application\Port\Secondary\MessageBusInterface;
use App\Application\Port\Secondary\RetrieveTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionInterface;
use App\Domain\TransactionStatusEnum;
use App\Tests\Unit\BaseUnitAbstract;
use Codeception\Stub\Expected;

/**
 * @covers \App\Application\Infrastructure\Service\ExternalTransactionHandler
 */
class ExternalTransactionHandlerTest extends BaseUnitAbstract
{
    protected static ?BankAccountInterface $sender = null;
    protected static ?TransactionInterface $transaction = null;

    public function testSuccessfullyTransferFoundsToExternalBankingSystem(): void
    {
        $senderCredit = 50;
        $senderReserved = 20;
        $amount = 10;
        $fee = 0.5;
        $awaitSet = false;
        self::$transaction = $this->makeEmpty(TransactionInterface::class, [
            'setStatus' => Expected::exactly(2, function (mixed $status) use (&$awaitSet) {
                $expected = TransactionStatusEnum::Finished;
                if (!$awaitSet) {
                    $expected = TransactionStatusEnum::Awaiting;
                    $awaitSet = true;
                }
                $this->tester->assertEquals($expected, $status);

                return self::$transaction;
            }),
            'getAmount' => $amount,
            'getCommissionFee' => $fee,
        ]);
        $retrieveTransactionRepositoryMock = $this->makeEmpty(RetrieveTransactionRepositoryInterface::class, [
            'get' => self::$transaction,
        ]);

        self::$sender = $this->makeEmpty(BankAccount::class, [
            'getCredit' => Expected::exactly(2, $senderCredit),
            'getReserved' => Expected::exactly(2, $senderReserved),
            'setCredit' => Expected::once(function (mixed $credit) use ($senderCredit, $amount, $fee): BankAccountInterface
            {
                $this->assertIsFloat($credit);
                $this->assertEquals($senderCredit - ($amount + $amount*$fee), $credit);

                return self::$sender;
            }),
            'setReserved' => Expected::once(function (mixed $reserved) use ($senderReserved, $amount, $fee): BankAccountInterface
            {
                $this->assertIsFloat($reserved);
                $this->assertEquals($reserved, $senderReserved - ($amount + $amount*$fee));

                return self::$sender;
            })
        ]);
        $bankAccountRepositoryMock = $this->makeEmpty(BankAccountRepositoryInterface::class, [
            'lockOptimistic' => self::$sender,
        ]);
        $databaseManagerMock = $this->makeEmpty(DatabaseManagerInterface::class, [
            'persist' => Expected::exactly(2),
        ]);
        $messageBusMock = $this->makeEmpty(MessageBusInterface::class, [
            'dispatch' => Expected::once(function (mixed $message) {
                $this->tester->assertInstanceOf(TransferCommissionFeeFoundsMessage::class, $message);
            }),
        ]);
        $externalBankClientMock = $this->makeEmpty(ExternalBankClientInterface::class);

        $handler = new ExternalTransactionHandler(
            $retrieveTransactionRepositoryMock,
            $bankAccountRepositoryMock,
            $databaseManagerMock,
            $messageBusMock,
            $externalBankClientMock,
        );

        $handler->handle(1);
    }

    public function testThrowOnNonExistingTransaction(): void
    {
        $handler = new ExternalTransactionHandler(
            $this->tester->getService(RetrieveTransactionRepositoryInterface::class),
            $this->makeEmpty(BankAccountRepositoryInterface::class),
            $this->makeEmpty(DatabaseManagerInterface::class),
            $this->makeEmpty(MessageBusInterface::class),
            $this->makeEmpty(ExternalBankClientInterface::class),
        );

        $this->expectException(TransactionNotFoundException::class);
        $handler->handle(0);
    }
}

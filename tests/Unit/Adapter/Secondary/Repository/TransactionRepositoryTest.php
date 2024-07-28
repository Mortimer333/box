<?php

declare(strict_types=1);

namespace App\Tests\Unit\Adapter\Secondary\Repository;

use App\Adapter\Secondary\DataFixtures\Test\BankAccountFixtures;
use App\Application\Infrastructure\Exception\TransactionNotFoundException;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\RetrieveTransactionRepositoryInterface;
use App\Application\Port\Secondary\StoreTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionInterface;
use App\Domain\CurrencyEnum;
use App\Domain\TransactionTypeEnum;
use App\Tests\Unit\BaseUnitAbstract;

/**
 * @covers \App\Adapter\Secondary\Repository\TransactionRepository
 */
class TransactionRepositoryTest extends BaseUnitAbstract
{
    public function testRetrieveSuccessfullyTransaction(): void
    {
        $repository = $this->tester->getService(RetrieveTransactionRepositoryInterface::class);
        $transaction = $repository->get(1);
        $this->assertInstanceOf(TransactionInterface::class, $transaction);
    }

    public function testExceptionIsThrownWhenRetrievingNonExistingTransaction(): void
    {
        $repository = $this->tester->getService(RetrieveTransactionRepositoryInterface::class);
        $this->expectException(TransactionNotFoundException::class);
        $repository->get(0);
    }

    public function testDailyTransactionsAreSummedProperly(): void
    {
        $now = new \DateTime();
        $repository = $this->tester->getService(RetrieveTransactionRepositoryInterface::class);
        $sum = $repository->retrieveSumBetweenDateWithoutFailures(
            new \DateTime($now->format('Y-m-d') . ' 00:00:00'),
            new \DateTime($now->format('Y-m-d') . ' 23:59:59'),
            1,
        );
        $this->tester->assertEquals(2, $sum);
    }

    public function testTransactionIsCreatedProperly(): void
    {
        $repository = $this->tester->getService(StoreTransactionRepositoryInterface::class);
        $bankRepository = $this->tester->getService(BankAccountRepositoryInterface::class);
        $sender = $bankRepository->get(1);
        $type = TransactionTypeEnum::Internal;
        $currency = CurrencyEnum::PLN;
        $amount = 10;
        $fee = .005;
        $title = 'Transaction #4';
        $receiver = 'John Smith';
        $receiverBankNumber = BankAccountFixtures::ACCOUNT_NUMBER_TWO;
        $address = 'address';
        $transaction = $repository->create(
            $sender,
            $type,
            $currency,
            $amount,
            $fee,
            $title,
            $receiver,
            $receiverBankNumber,
            $address,
        );

        $this->assertEquals($sender, $transaction->getSender());
        $this->assertEquals($type, $transaction->getType());
        $this->assertEquals($currency, $transaction->getCurrency());
        $this->assertEquals($amount, $transaction->getAmount());
        $this->assertEquals($fee, $transaction->getCommissionFee());
        $this->assertEquals($title, $transaction->getTitle());
        $this->assertEquals($receiver, $transaction->getReceiver());
        $this->assertEquals($receiverBankNumber, $transaction->getReceiverAccountNumber());
        $this->assertEquals($address, $transaction->getAddress());
    }
}

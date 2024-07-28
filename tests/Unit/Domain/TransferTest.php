<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\ConfigurationException;
use App\Tests\Unit\BaseUnitAbstract;

/**
 * @covers \App\Domain\Transfer
 */
class TransferTest extends BaseUnitAbstract
{
    public function testCommissionFeeCanBeAppliedOnlyOnce(): void
    {
        $startingAmount = 10;
        $transfer = $this->tester->generateTransfer(amount: $startingAmount);
        $this->tester->assertEquals($startingAmount, $transfer->getAmount());
        $transfer->applyCommissionFee();
        $increasedAmount = $transfer->getAmount();
        $this->tester->assertNotEquals($startingAmount, $transfer->getAmount());
        $transfer->applyCommissionFee();
        $this->tester->assertEquals($increasedAmount, $transfer->getAmount());
    }

    public function testCommissionFeeExceptionIsThrownWithFaultConfig(): void
    {
        $_ENV['COMMISSION_FEE'] = null;
        $this->expectException(ConfigurationException::class);
        $transfer = $this->tester->generateTransfer();
        $transfer->applyCommissionFee();
    }

    public function testDailyTransactionLimitExceptionIsThrownWithFaultConfig(): void
    {
        $_ENV['MAX_DAILY_TRANSACTION_LIMIT'] = null;
        $this->expectException(ConfigurationException::class);
        $transfer = $this->tester->generateTransfer();
        $transfer->getDailyTransactionLimit();
    }

    public function testFoundsAreTransferredSuccessfully(): void
    {
        $amount = 10;
        $startingSenderCredit = $senderCredit = 20;
        $startingReceiverCredit = $receiverCredit = 20;
        $transfer = $this->tester->generateTransfer(amount: $amount);
        $transfer->transferFounds($senderCredit, $receiverCredit);
        $this->tester->assertEquals($startingSenderCredit - $amount, $senderCredit);
        $this->tester->assertEquals($startingReceiverCredit + $amount, $receiverCredit);
    }
}

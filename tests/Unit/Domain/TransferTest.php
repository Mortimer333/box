<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\ConfigurationException;
use App\Tests\Unit\BaseUnitAbstract;
use Codeception\Attribute\Examples;

/**
 * @covers \App\Domain\Transfer
 */
class TransferTest extends BaseUnitAbstract
{
    public function testCommissionFeeExceptionIsThrownWithFaultConfig(): void
    {
        $_ENV['COMMISSION_FEE'] = null;
        $this->expectException(ConfigurationException::class);
        $transfer = $this->tester->generateTransfer();
        $transfer->getCommissionFeeAmount();
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
        $commissionFee = 0;
        $transfer->transferFounds($senderCredit, $receiverCredit, $commissionFee);
        $this->tester->assertEquals($transfer->getCommissionFeeAmount(), $commissionFee);
        $this->tester->assertEquals($startingSenderCredit - $amount - $commissionFee, $senderCredit);
        $this->tester->assertEquals($startingReceiverCredit + $amount, $receiverCredit);
    }

    #[Examples(10, 20, 0, 0, true)]
    #[Examples(20, 10, 0, 0, false)]
    #[Examples(10, 20, 11, 0, false)]
    #[Examples(10, 20, 9, 0.5, false)]
    #[Examples(10, 20, 8, 0.05, true)]
    public function testFoundsSufficiencyProperlyCalculated(
        float $amount,
        float $credit,
        float $reserved,
        float $commissionFee,
        bool $expected,
    ): void {
        $previousFee = $_ENV['COMMISSION_FEE'] ?? 0;
        $_ENV['COMMISSION_FEE'] = $commissionFee;
        $transfer = $this->tester->generateTransfer(
            amount: $amount,
            senderBankAccountCredit: $credit,
            senderBankAccountReserved: $reserved,
        );

        $this->tester->assertEquals($expected, $transfer->senderHasEnoughCredit());

        $_ENV['COMMISSION_FEE'] = $previousFee;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\ConfigurationException;
use App\Tests\Unit\BaseUnitAbstract;
use Codeception\Attribute\Examples;

/**
 * @covers \App\Domain\ExternalTransfer
 */
class ExternalTransferTest extends BaseUnitAbstract
{

    public function testCommissionFeeExceptionIsThrownWithFaultConfig(): void
    {
        $_ENV['EXTERNAL_COMMISSION_FEE'] = null;
        $this->expectException(ConfigurationException::class);
        $transfer = $this->tester->generateExternalTransfer();
        $transfer->getCommissionFeeAmount();
    }

    public function testFoundsAreTransferredSuccessfully(): void
    {
        $amount = 10;
        $startingSenderCredit = $senderCredit = 20;
        $startingReservedCredit = $reservedCredit = 20;
        $transfer = $this->tester->generateExternalTransfer(amount: $amount);
        $feeCredit = 0;
        $transfer->finishFoundsTransfer($senderCredit, $reservedCredit, $feeCredit);
        $this->tester->assertEquals($transfer->getCommissionFeeAmount(), $feeCredit);
        $this->tester->assertEquals(
            $startingSenderCredit - $amount - $transfer->getCommissionFeeAmount(),
            $senderCredit,
        );
        $this->tester->assertEquals(
            $startingReservedCredit - $amount - $transfer->getCommissionFeeAmount(),
            $reservedCredit,
        );
    }

    public function testFoundsAreTransferredSuccessfullyWithManuallyProvidedFee(): void
    {
        $amount = 10;
        $fee = 0.1;
        $amountFee = $amount * $fee;
        $startingSenderCredit = $senderCredit = 20;
        $startingReservedCredit = $reservedCredit = 20;
        $transfer = $this->tester->generateExternalTransfer(amount: $amount, commissionFee: $fee);
        $feeCredit = 0;
        $transfer->finishFoundsTransfer($senderCredit, $reservedCredit, $feeCredit);
        $this->tester->assertEquals($amountFee, $feeCredit);
        $this->tester->assertEquals(
            $startingSenderCredit - $amount - $amountFee,
            $senderCredit,
        );
        $this->tester->assertEquals(
            $startingReservedCredit - $amount - $amountFee,
            $reservedCredit,
        );
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
        $transfer = $this->tester->generateExternalTransfer(
            amount: $amount,
            senderBankAccountCredit: $credit,
            senderBankAccountReserved: $reserved,
            commissionFee: $commissionFee,
        );

        $this->tester->assertEquals($expected, $transfer->senderHasEnoughCredit());
    }
}

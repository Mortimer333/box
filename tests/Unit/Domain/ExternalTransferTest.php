<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Tests\Unit\BaseUnitAbstract;

/**
 * @covers \App\Domain\ExternalTransfer
 */
class ExternalTransferTest extends BaseUnitAbstract
{
    public function testFoundsAreTransferredSuccessfully(): void
    {
        $amount = 10;
        $startingSenderCredit = $senderCredit = 20;
        $startingReservedCredit = $reservedCredit = 20;
        $transfer = $this->tester->generateExternalTransfer(amount: $amount);
        $transfer->finishFoundsTransfer($senderCredit, $reservedCredit);
        $this->tester->assertEquals($startingSenderCredit - $amount, $senderCredit);
        $this->tester->assertEquals($startingReservedCredit - $amount, $reservedCredit);
    }
}

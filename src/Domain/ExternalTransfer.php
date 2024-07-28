<?php

declare(strict_types=1);

namespace App\Domain;

final class ExternalTransfer
{
    public function __construct(
        public readonly ExternalTransferSender $sender,
        public float $amount,
    ) {
    }

    public function finishFoundsTransfer(float &$credit, float &$reserved): void
    {
        $credit -= $this->amount;
        $reserved -= $this->amount;
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Domain\Transfer;

/**
 * Proxy class.
 * To prevent changing implementation outside the chain (in case we would like to start form different point) root class
 * prevents unnecessary changes across the system and keeping them to single place like an anchor.
 * In simple terms:
 * Instead of changing initializations of the CoR across all related classes we just change class in the constructor.
 */
class TransferChainRoot
{
    public function __construct(
        protected RaiseAndValidateTransferAmount $start,
    ) {
    }

    public function process(Transfer $transfer, BankAccountInterface $sender): void
    {
        $this->start->process($transfer, $sender);
    }
}

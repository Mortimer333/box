<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\NotEnoughCreditException;
use App\Domain\Transfer;

final readonly class ValidateExternalTransferAmount implements TransactionChainLinkInterface
{
    public function __construct(
        private TransactionChainLinkInterface $next,
    ) {
    }

    public function process(
        Transfer $transfer,
        BankAccountInterface $sender,
    ): void {
        $external = $transfer->convertToExternal();
        if (!$external->senderHasEnoughCredit()) {
            throw new NotEnoughCreditException($transfer->amount, $transfer->currency);
        }

        $this->next->process($transfer, $sender);
    }
}

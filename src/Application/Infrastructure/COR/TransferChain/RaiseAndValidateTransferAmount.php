<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\NotEnoughCreditException;
use App\Domain\Transfer;

final readonly class RaiseAndValidateTransferAmount implements TransactionChainLinkInterface
{
    public function __construct(
        private TransactionChainLinkInterface $next,
    ) {
    }

    public function process(
        Transfer $transfer,
        BankAccountInterface $sender,
    ): void {
        $transfer->applyCommissionFee();
        if (!$transfer->senderHasEnoughCredit()) {
            throw new NotEnoughCreditException($transfer->getAmount(), $transfer->currency);
        }

        $this->next->process($transfer, $sender);
    }
}

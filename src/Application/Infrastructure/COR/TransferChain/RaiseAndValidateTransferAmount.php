<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Domain\NotEnoughCreditException;
use App\Domain\Transfer;

final readonly class RaiseAndValidateTransferAmount
{
    public function __construct(
        private ClientDailyTransferLimitValidation $clientDailyTransferLimitValidation,
    ) {
    }

    public function process(Transfer $transfer, BankAccountInterface $sender): void
    {
        $transfer->applyCommissionFee();
        if (!$transfer->senderHasEnoughCredit()) {
            throw new NotEnoughCreditException($transfer->getAmount(), $transfer->currency);
        }

        $this->clientDailyTransferLimitValidation->process($transfer, $sender);
    }
}

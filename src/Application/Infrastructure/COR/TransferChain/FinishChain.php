<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\Transfer;
use Psr\Log\LoggerInterface;

final readonly class FinishChain implements TransactionChainLinkInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function process(
        Transfer $transfer,
        BankAccountInterface $sender,
    ): void {
        $this->logger->info(
            sprintf(
                'Transfer proces from %s to %s finished successfully',
                $transfer->sender->bankAccountNumber,
                $transfer->receiver->bankAccountNumber
            ),
        );
    }
}

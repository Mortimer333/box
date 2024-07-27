<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Domain\Transfer;
use Psr\Log\LoggerInterface;

final readonly class FinishChain
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function process(Transfer $transfer): void
    {
        $this->logger->info(
            sprintf(
                'Transfer from %s to %s finished successfully',
                $transfer->sender->bankAccountNumber,
                $transfer->receiver->bankAccountNumber
            ),
        );
    }
}

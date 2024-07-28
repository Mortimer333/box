<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\DailyLimitExceededException;
use App\Domain\Transfer;

final readonly class ClientDailyTransferLimitExceeded implements TransactionChainLinkInterface
{
    public function __construct(
        private TransactionChainLinkInterface $next,
    ) {
    }

    public function process(
        Transfer $transfer,
        BankAccountInterface $sender,
    ): void {
        $file = fopen('/app/var/test', 'a');
        fwrite($file, 'daily' . PHP_EOL);
        fclose($file);
        if ($transfer->hasClientReachedHisDailyLimit()) {
            throw new DailyLimitExceededException($transfer->getDailyTransactionLimit());
        }

        $this->next->process($transfer, $sender);
    }
}

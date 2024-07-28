<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\MessageHandler;

use App\Application\Infrastructure\Message\TransferCommissionFeeFoundsMessage;

/**
 * @codeCoverageIgnore
 */
final readonly class TransferCommissionFeeFoundsMessageHandler
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(TransferCommissionFeeFoundsMessage $message): void
    {
        // @TODO Do smth with commission fee, probably mark it somewhere? Like an internal bank account?
    }
}

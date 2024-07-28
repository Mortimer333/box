<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Message;

/**
 * @codeCoverageIgnore
 */
final readonly class TransferCommissionFeeFoundsMessage
{
    public function __construct(public float $amount)
    {
    }
}

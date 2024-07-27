<?php

declare(strict_types=1);

namespace App\Domain;

class CurrencyMismatchException extends \DomainException
{
    public function __construct(
        CurrencyEnum $sender,
        CurrencyEnum $receiver,
    ) {
        parent::__construct(
            sprintf(
                'Service cannot exchange from currency %s to %s',
                \mb_strtoupper($sender->value),
                \mb_strtoupper($receiver->value),
            ),
            400
        );
    }
}

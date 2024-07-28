<?php

declare(strict_types=1);

namespace App\Domain;

final class NotEnoughCreditException extends \DomainException
{
    public function __construct(
        float $amount,
        CurrencyEnum $currency,
    ) {
        // I know that my currencies will look like 12 USD instead of $12, but I'm intentionally skipping creation
        // of price beautifier to focus on important parts of the task
        parent::__construct(
            sprintf(
                'Chosen account doesn\'t have enough credit to trasfer %s %s',
                $amount,
                \mb_strtoupper($currency->value),
            ),
            400
        );
    }
}

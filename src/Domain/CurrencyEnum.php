<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * For the sake of the task I will be keeping available currencies in Enum as opposed to the table or retrieving them
 * from separate service.
 */
enum CurrencyEnum: string
{
    case USD = 'usd';
    case PLN = 'pln';
}

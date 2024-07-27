<?php

declare(strict_types=1);

namespace App\Domain;

enum TransactionStatusEnum: string
{
    case Processing = 'processing';

    /**
     * When awaiting response from 3rd party system.
     */
    case Awaiting = 'awaiting';

    case Finished = 'finished';

    case Failed = 'failed';
}

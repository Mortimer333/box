<?php

declare(strict_types=1);

namespace App\Domain;

enum TransactionStatusEnum: string
{
    case Send = 'send';
    case Delivered = 'delivered';
}

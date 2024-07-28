<?php

declare(strict_types=1);

namespace App\Domain;

final class DailyLimitExceededException extends \DomainException
{
    public function __construct(
        int $dailyLimit,
    ) {
        parent::__construct(
            sprintf(
                'You have reached your daily limit of %s transaction, wait until tomorrow to do another',
                $dailyLimit
            ),
            400
        );
    }
}

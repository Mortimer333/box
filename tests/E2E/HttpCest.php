<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Tests\Support\E2ETester;
use Zenstruck\Messenger\Test\Transport\TestTransport;

class HttpCest extends AbstractCest
{
    protected TestTransport $messenger;

    public function _before(E2ETester $I): void
    {
        parent::_before($I);
    }
}

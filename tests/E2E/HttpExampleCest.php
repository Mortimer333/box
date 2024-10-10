<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Tests\Support\E2ETester;
use Codeception\Attribute\Skip;
use Codeception\Util\HttpCode;

class HttpExampleCest extends AbstractCest
{
    #[Skip]
    public function testExample(E2ETester $I): void
    {
        $I->request('/test', 'POST', [
            'test' => 10,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}

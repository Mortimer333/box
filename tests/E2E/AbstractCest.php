<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Adapter\Secondary\DataFixtures\Test\UserFixtures;
use App\Tests\Support\E2ETester;

abstract class AbstractCest
{
    public function _before(E2ETester $I): void
    {
        $I->haveHttpHeader(
            'Authorization',
            'Bearer ' . base64_encode(UserFixtures::USER_EMAIL . ':' . UserFixtures::USER_PLAIN_PASSWORD),
        );
    }

    protected function logout(E2ETester $I): void
    {
        $I->logout();
        $I->haveHttpHeader('Authorization', '');
    }
}

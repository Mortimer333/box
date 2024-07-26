<?php

// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Support\IntegrationTester;
use Codeception\Test\Unit;

class BaseIntegrationAbstract extends Unit
{
    protected IntegrationTester $tester;
    /** @var array<int, object> $toRemove */
    protected array $toRemove = [];

    public function _after(): void
    {
        $this->tester->removeSavedEntities();
    }
}

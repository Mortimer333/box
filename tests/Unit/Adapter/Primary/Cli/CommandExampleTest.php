<?php

declare(strict_types=1);

namespace App\Tests\Unit\Adapter\Primary\Cli;

use App\Tests\Unit\BaseUnitAbstract;
use Codeception\Attribute\Skip;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Template for command testing.
 */
class CommandExampleTest extends BaseUnitAbstract
{
    #[Skip]
    public function testExample(): void
    {
        $command = $this->tester->getService(Command::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }
}

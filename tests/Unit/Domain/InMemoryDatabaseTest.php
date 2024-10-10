<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Application\Port\Secondary\InMemoryTableInterface;
use App\Domain\CommandHandler;
use App\Domain\NoTransactionException;
use App\Domain\Table;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;

class InMemoryDatabaseTest extends Unit
{
    public UnitTester $tester;
    private InMemoryTableInterface $table;
    private CommandHandler $handler;

    public function _before(): void
    {
        $this->table = new Table('test');
        $this->handler = new CommandHandler($this->table);
    }

    public function testGetSetAndDeleteCommand(): void
    {
        $this->handler->process('SET a 10');
        $output = $this->handler->process('GET a');
        $this->tester->assertSame('10', $output);
        $this->handler->process('DELETE a');
        $output = $this->handler->process('GET a');
        $this->tester->assertTrue(is_null($output));
    }

    public function testCountCommand(): void
    {
        $this->handler->process('SET a 10');
        $this->handler->process('SET b 10');
        $output = $this->handler->process('COUNT 10');
        $this->tester->assertSame(2, $output);
        $output = $this->handler->process('COUNT 20');
        $this->tester->assertSame(0, $output);
        $this->handler->process('DELETE a');
        $output = $this->handler->process('COUNT 10');
        $this->tester->assertSame(1, $output);
        $this->handler->process('SET b 30');
        $output = $this->handler->process('COUNT 10');
        $this->tester->assertSame(0, $output);
    }

    public function testTransaction(): void
    {
        $this->handler->process('BEGIN');
        $this->handler->process('SET a 10');
        $output = $this->handler->process('GET a');
        $this->tester->assertSame('10', $output);
        $this->handler->process('BEGIN');
        $this->handler->process('SET a 20');
        $output = $this->handler->process('GET a');
        $this->tester->assertSame('20', $output);
        $this->handler->process('ROLLBACK');
        $output = $this->handler->process('GET a');
        $this->tester->assertSame('10', $output);
        $this->handler->process('ROLLBACK');
        $output = $this->handler->process('GET a');
        $this->tester->assertTrue(is_null($output));
    }

    public function testTransactions2(): void
    {
        $this->handler->process('BEGIN');
        $this->handler->process('SET a 30');
        $this->handler->process('BEGIN');
        $this->handler->process('SET a 40');
        $this->handler->process('COMMIT');
        $output = $this->handler->process('GET a');
        $this->tester->assertSame('40', $output);
        $this->expectException(NoTransactionException::class);
        $this->handler->process('ROLLBACK');
    }

    public function testTransactions3(): void
    {
        $this->handler->process('SET a 50');
        $this->handler->process('BEGIN');
        $output = $this->handler->process('GET a');
        $this->tester->assertSame('50', $output);
        $this->handler->process('SET a 60');
        $this->handler->process('BEGIN');
        $this->handler->process('DELETE a');
        $output = $this->handler->process('GET a');
        $this->tester->assertTrue(is_null($output));
        $this->handler->process('ROLLBACK');
        $output = $this->handler->process('GET a');
        $this->tester->assertSame('60', $output);
        $this->handler->process('COMMIT');
        $output = $this->handler->process('GET a');
        $this->tester->assertSame('60', $output);
    }

    public function testTransactions4(): void
    {
        $this->handler->process('SET a 10');
        $this->handler->process('BEGIN');
        $output = $this->handler->process('COUNT 10');
        $this->tester->assertSame(1, $output);
        $this->handler->process('BEGIN');
        $this->handler->process('DELETE a');
        $output = $this->handler->process('COUNT 10');
        $this->tester->assertSame(0, $output);
        $this->handler->process('ROLLBACK');
        $output = $this->handler->process('COUNT 10');
        $this->tester->assertSame(1, $output);
    }
}

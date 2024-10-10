<?php

declare(strict_types=1);

namespace App\Domain;

use App\Application\Port\Secondary\InMemoryTableInterface;

final class CommandHandler
{
    public function __construct(
        private InMemoryTableInterface $table,
    ) {
    }

    /**
     * @throws NoTransactionException
     */
    public function process(string $command): mixed
    {
        $chunks = explode(' ', $command);
        $operation = $chunks[0];
        return match($operation) {
            'BEGIN' => $this->begin(),
            'COMMIT' => $this->commit(),
            'ROLLBACK' => $this->rollback(),
            'GET' => $this->get($chunks[1] ?? throw new InvalidCommandException('INVALID GET COMMAND - Missing name')),
            'SET' => $this->set(
                $chunks[1] ?? throw new InvalidCommandException('INVALID SET COMMAND - Missing name'),
                $chunks[2] ?? throw new InvalidCommandException('INVALID SET COMMAND - Missing value'),
            ),
            'DELETE' => $this->delete($chunks[1] ?? throw new InvalidCommandException('INVALID DELETE COMMAND - Missing name')),
            'COUNT' => $this->count($chunks[1] ?? throw new InvalidCommandException('INVALID COUNT COMMAND - Missing name')),
        };
    }

    private function commit(): void
    {
        $this->table = $this->table->commit();
    }

    private function begin(): void
    {
        $this->table = $this->table->begin();
    }

    private function rollback(): void
    {
        $this->table = $this->table->rollback();
    }

    private function get(string $name): mixed
    {
        return $this->table->get($name);
    }

    /**
     * @TODO implement caster as currently all values are stored as strings
     */
    private function set(string $name, string $value): void
    {
        $this->table->set($name, $value);
    }

    private function delete(string $name): void
    {
        $this->table->delete($name);
    }

    private function count(string $name): mixed
    {
        return $this->table->count($name);
    }
}

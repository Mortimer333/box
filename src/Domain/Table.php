<?php

declare(strict_types=1);

namespace App\Domain;

use App\Application\Port\Secondary\InMemoryTableInterface;

class Table implements InMemoryTableInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $hashMap = [];


    public function __construct(
        protected string $name,
    ) {
    }

    public function getMap(): array
    {
        return $this->hashMap;
    }

    /**
     * @todo refactor, this is linear search
     */
    public function count(mixed $value): int
    {
        $amount = 0;
        foreach ($this->getMap() as $item) {
            if ($item === $value) {
                $amount++;
            }
        }

        return $amount;
    }

    public function get(string $name): mixed
    {
        return $this->hashMap[$name] ?? null;
    }

    public function set(string $name, mixed $value): static
    {
        $old = $this->get($name);
        if ($old === $value) {
            return $this;
        }

        $this->hashMap[$name] = $value;

        return $this;
    }

    public function delete(string $name): static
    {
        if (isset($this->hashMap[$name])) {
            unset($this->hashMap[$name]);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function begin(): TmpTable
    {
        return new TmpTable($this);
    }

    /**
     * @return never
     */
    public function rollback()
    {
        throw new NoTransactionException();
    }

    /**
     * @return never
     */
    public function commit()
    {
        throw new NoTransactionException();
    }
}

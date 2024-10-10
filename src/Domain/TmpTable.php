<?php

declare(strict_types=1);

namespace App\Domain;

use App\Application\Port\Secondary\InMemoryTableInterface;

final class TmpTable implements InMemoryTableInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $overlayHashMap = [];

    public function __construct(
        private InMemoryTableInterface $primary
    ) {
    }

    /**
     * @TODO refactor this as this is linear search, way above O(logn)
     */
    public function count(mixed $value): int
    {
        $map = [...$this->primary->getMap(), ...$this->getMap()];
        $amount = 0;
        foreach ($map as $item) {
            if ($item === $value) {
                $amount++;
            }
        }

        return $amount;
    }

    public function getMap(): array
    {
        return $this->overlayHashMap;
    }

    public function get(string $name): mixed
    {
        if (array_key_exists($name, $this->overlayHashMap)) {
            return $this->overlayHashMap[$name];
        }

        return $this->primary->get($name);
    }

    public function set(string $name, mixed $value): static
    {
        $this->overlayHashMap[$name] = $value;

        return $this;
    }

    public function delete(string $name): static
    {
        $this->overlayHashMap[$name] = null;

        return $this;
    }

    public function getName(): string
    {
        return $this->primary->getName();
    }

    public function begin(): TmpTable
    {
        return new TmpTable($this);
    }

    public function rollback(): InMemoryTableInterface
    {
        return $this->primary;
    }

    public function commit(): InMemoryTableInterface
    {
        if ($this->primary instanceof TmpTable) {
            $this->primary = $this->primary->commit();
        }

        foreach ($this->overlayHashMap as $name => $value) {
            if (is_null($value)) {
                $this->primary->delete($name);
                continue;
            }

            $this->primary->set($name, $value);
        }

        return $this->primary;
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Domain\TmpTable;

interface InMemoryTableInterface
{
    public function get(string $name): mixed;

    public function count(mixed $value): int;

    public function set(string $name, mixed $value): static;

    public function delete(string $name): static;

    public function getName(): string;

    public function begin(): TmpTable;
}

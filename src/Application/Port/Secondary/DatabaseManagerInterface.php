<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

interface DatabaseManagerInterface
{
    public function persist(): void;
}

<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

interface MessageBusInterface
{
    public function dispatch(object $message): void;
}

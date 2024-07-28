<?php

declare(strict_types=1);

namespace App\Tests\Support\Override;

use App\Application\Port\Secondary\MessageBusInterface;

class MessageBus implements MessageBusInterface
{
    public static ?MessageBusInterface $mock = null;

    public function __construct(
        protected MessageBusInterface $default,
    ) {
    }

    public function getManager(): MessageBusInterface
    {
        return self::$mock ?? $this->default;
    }

    public function dispatch(object $message): void
    {
        $this->getManager()->dispatch($message);
    }
}

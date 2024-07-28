<?php

declare(strict_types=1);

namespace App\Adapter\Primary\Queue;

use App\Application\Port\Secondary\MessageBusInterface as AdapterMessageBusInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Very simple, almost mock implementation, just to showcase framework-agnostic approach.
 */
class MessageBus implements AdapterMessageBusInterface
{
    public function __construct(
        protected MessageBusInterface $messageBus,
    ) {
    }

    public function dispatch(object $message): void
    {
        $this->messageBus->dispatch($message);
    }
}

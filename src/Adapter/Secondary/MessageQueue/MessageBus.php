<?php

declare(strict_types=1);

namespace App\Adapter\Secondary\MessageQueue;

use App\Application\Port\Secondary\MessageBusInterface as PortMessageBusInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @codeCoverageIgnore
 * Very simple, almost mock implementation, just to keep framework-agnostic approach.
 * Would more robust but lack of time and actual need resulted in very simplistic approach.
 */
final readonly class MessageBus implements PortMessageBusInterface
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

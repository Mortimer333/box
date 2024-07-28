<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\MessageHandler;

use App\Application\Infrastructure\Message\ProcessTransactionMessage;
use App\Application\Port\Secondary\TransactionHandlerInterface;
use Psr\Log\LoggerInterface;

class ProcessTransactionMessageHandler
{
    public function __construct(
        protected LoggerInterface $logger,
        protected TransactionHandlerInterface $transactionHandler,
    ) {
    }

    public function __invoke(ProcessTransactionMessage $message): void
    {
        try {
            $this->transactionHandler->handle($message->transactionId);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ]);
            // And probably something more like an email to respond team.

            // I'm intentionally not implementing any handle to restore reserved amount back to user credit.
            // This situation must be manually verified or there should be some additional business logic how to
            // handle it.

            // Propagate the exception, so the Rabbit worker will retry running the handler
            throw $e;
        }
    }
}

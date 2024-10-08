<?php

declare(strict_types=1);

namespace App\Adapter\Primary\Http\EventListener;

use App\Application\Exception\AuthenticationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * @codeCoverageIgnore Helper class, probably wouldn't make it to production but helps a lot for testing
 * Response transformer to make debugging easier.
 */
final class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 1],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $status = $this->getStatusCode($exception);
        $event->setResponse(new JsonResponse(
            [
                'message' => $exception->getMessage(),
                'status' => $status,
                'success' => false,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ],
            $status,
        ));
    }

    protected function getStatusCode(\Throwable $exception): int
    {
        // HttpExceptionInterface is a special exception because it holds status code differently
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        if (
            $exception instanceof AuthenticationException
            || $exception instanceof CustomUserMessageAuthenticationException
        ) {
            return Response::HTTP_UNAUTHORIZED;
        }

        return $exception->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}

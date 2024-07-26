<?php

declare(strict_types=1);

namespace App\Tests\Support\Override;

use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Template for mocking whole clients and their responses
 */
abstract class ClientMock
{
    /** @var MockResponse[] $responses */
    protected static array $responses = [];

    abstract public static function getBase(): string;

    /**
     * @param array<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->base->$name(...$arguments); // @phpstan-ignore-line
    }

    /**
     * @param array<mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return forward_static_call([static::getBase(), $name], ...$arguments); // @phpstan-ignore-line
    }

    /**
     * @param array<MockResponse> $responses
     */
    public static function setResponses(array $responses): void
    {
        static::$responses = $responses;
    }

    public static function addResponse(MockResponse $response): void
    {
        static::$responses[] = $response;
    }

    public static function clearResponses(): void
    {
        static::$responses = [];
    }

    /**
     * @return MockResponse[]
     */
    public static function getResponses(): array
    {
        return static::$responses;
    }
}

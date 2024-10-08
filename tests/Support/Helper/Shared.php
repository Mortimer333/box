<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Zenstruck\Messenger\Test\Transport\TestTransport;
use Zenstruck\Messenger\Test\Transport\TestTransportRegistry;

class Shared extends \Codeception\Module
{
    /**
     * @template T
     *
     * @param class-string<T> $service
     *
     * @return T
     *
     * @throws \Exception
     */
    public function getService(string $service)
    {
        return $this->getModule('Symfony')->_getContainer()->get($service);
    }

    public function getMessenger(?string $transport = null): TestTransport
    {
        /** @var TestTransportRegistry $registry */
        $registry = $this->getService('zenstruck_messenger_test.transport_registry');

        return $registry->get($transport);
    }
}

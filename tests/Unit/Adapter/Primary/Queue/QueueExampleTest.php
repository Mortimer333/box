<?php

declare(strict_types=1);

namespace App\Tests\Unit\Adapter\Primary\Queue;

use App\Tests\Unit\BaseUnitAbstract;
use Codeception\Attribute\Skip;
use Zenstruck\Messenger\Test\Transport\TestTransport;

class QueueExampleTest extends BaseUnitAbstract
{
    protected TestTransport $messenger;

    public function _before(): void
    {
        parent::_before();
        $this->messenger = $this->tester->getMessenger('bus');
        $this->messenger->reset();
    }

    #[Skip]
    public function testExampleOfQueueTest(): void
    {
        $this->messenger->throwExceptions();
        $this->messenger->queue()->assertContains(Message::class);
        $this->messenger->process(1);
        $rejected = $this->messenger->rejected();
        $this->tester->assertSame(0, $rejected->count());
        $this->messenger->queue()->assertCount(1);
        $this->messenger->queue()->assertEmpty();
    }
}

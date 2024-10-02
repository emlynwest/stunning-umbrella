<?php

namespace App\Tests\Unit\Service\Webhook;

use App\Model\Webhook;
use App\Service\Webhook\Dispatcher;
use App\Service\Webhook\Worker;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DispatcherTest extends TestCase
{
    use ProphecyTrait;

    protected Dispatcher $dispatcher;

    /** @var Worker|\Prophecy\Prophecy\ObjectProphecy  */
    protected $workerMock;

    public function setUp(): void
    {
        $this->workerMock = $this->prophesize(Worker::class);

        $this->dispatcher = new Dispatcher($this->workerMock->reveal());
    }

    public function testDispatch(): void
    {
        $webhook1 = new Webhook('https://a.com', 1, 'Hook 1', 'First event');
        $webhook2 = new Webhook('https://b.com', 2, 'Hook 2', 'Second event');

        $webhooks = [
            $webhook1,
            $webhook2,
        ];

        $this->workerMock->process($webhook1)
            ->shouldBeCalledOnce();

        $this->workerMock->process($webhook2)
            ->shouldBeCalledOnce();

        $this->dispatcher->dispatch($webhooks);
    }
}

<?php

namespace App\Tests\Unit\Service\Webhook;

use App\Model\Webhook;
use App\Service\Webhook\Dispatcher;
use App\Service\Webhook\Worker;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DispatcherTest extends TestCase
{
    use ProphecyTrait;

    protected Dispatcher $dispatcher;

    protected Worker|ObjectProphecy $workerMock;

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
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->workerMock->process($webhook2)
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->dispatcher->dispatch($webhooks);
    }

    public function testFailedDispatchesStopsProcessing(): void
    {
        $url = 'https://a.com';

        $webhooks = [
            new Webhook($url, 1, 'Hook 1', 'First event'),
            new Webhook($url, 2, 'Hook 2', 'Second event'),
            new Webhook($url, 3, 'Hook 3', 'Third event'),
            new Webhook($url, 4, 'Hook 4', 'Fourth event'),
            new Webhook($url, 5, 'Hook 5', 'Fifth event'),
            new Webhook($url, 5, 'Hook 6', 'Sixth event'),
        ];

        $this->workerMock->process(Argument::any())
            ->shouldBeCalledTimes(5)
            ->willReturn(false);

        $this->dispatcher->dispatch($webhooks);
    }
}

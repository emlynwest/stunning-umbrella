<?php

namespace App\Tests\Unit\Service\Webhook;

use App\Model\Webhook;
use App\Service\Webhook\Worker;
use Carbon\Carbon;
use LogicException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Response\MockResponse;

class WorkerTest extends TestCase
{
    use ProphecyTrait;

    protected HttpClientInterface|ObjectProphecy $httpClientMock;

    protected function createWorker(
        int $maxTimeout = 60,
        float $backoffMultiplier = 2,
        int $initialBackoffTime = 1): Worker
    {
        $this->httpClientMock = $this->prophesize(HttpClientInterface::class);

        $logger = $this->prophesize(LoggerInterface::class);

        return new Worker(
            $this->httpClientMock->reveal(),
            $logger->reveal(),
            $maxTimeout,
            $backoffMultiplier,
            $initialBackoffTime
        );
    }

    /**
     * @param float $backoffMultiplier
     * @dataProvider invalidBackoffTimeProvider
     */
    public function testInitialBackoffTimeCantBeLessThanOne(float $backoffMultiplier): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Backoff multiplier must be greater than 1');

        $this->createWorker(backoffMultiplier: $backoffMultiplier);
    }

    public static function invalidBackoffTimeProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
            [0.999999],
        ];
    }

    public function testSuccessfulProcess(): void
    {
        $url = 'https://foobar.com';
        $webhook = new Webhook($url, 1, 'test', 'test');

        $worker = $this->createWorker();

        $this->httpClientMock->request('GET', $url)
            ->shouldBeCalledOnce()
            ->willReturn(new MockResponse('{}', ['http_code' => 200]));

        $result = $worker->process($webhook);

        $this->assertTrue($result);
    }

    public function testFailedRequestBacksOff(): void
    {
        $url = 'https://foobar.com';
        $webhook = new Webhook($url, 1, 'test', 'test');

        // A max timeout of 5 should result in 3 requests before stopping(1, 2 then 4 seconds wait)
        $worker = $this->createWorker(maxTimeout: 5);

        $now = Carbon::now();
        Carbon::setTestNow($now);

        $this->httpClientMock->request('GET', $url)
            ->shouldBeCalledTimes(3)
            ->willReturn(new MockResponse('{}', ['http_code' => 500]));

        $result = $worker->process($webhook);

        $this->assertFalse($result);

        // We should have ended up delaying a total of 7 seconds so check for that
        $now->addSeconds(7);
        $this->assertEquals($now, Carbon::getTestNow());
    }
}

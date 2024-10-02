<?php

namespace App\Tests\Unit\Service\Webhook;

use App\Service\Webhook\Reader;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * Location of the test data file.
     * Ideally this should be config based or the actual file reading can be mocked with vfsStream
     * @var string
     */
    protected string $fileLocation = '/app/tests/data/webhooks.txt';

    protected Reader $reader;

    protected function setUp(): void
    {
        $this->reader = new Reader();
    }

    public function testLoadWebhooks(): void
    {
        $webhooks = $this->reader->loadWebhooks($this->fileLocation);

        $this->assertCount(2, $webhooks);

        $this->assertEquals('https://a.com', $webhooks[0]->getUrl());
        $this->assertEquals(1, $webhooks[0]->getOrderId());
        $this->assertEquals('Name One', $webhooks[0]->getName());
        $this->assertEquals('Event One', $webhooks[0]->getEvent());

        $this->assertEquals('https://b.com', $webhooks[1]->getUrl());
        $this->assertEquals(2, $webhooks[1]->getOrderId());
        $this->assertEquals('Name Two', $webhooks[1]->getName());
        $this->assertEquals('Event Two', $webhooks[1]->getEvent());
    }
}

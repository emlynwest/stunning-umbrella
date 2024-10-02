<?php

namespace App\Tests\Unit\Model;

use App\Model\Webhook;
use PHPUnit\Framework\TestCase;

class WebhookTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $url = 'http://foobar.com';
        $orderId = 1;
        $name = 'Webhook1';
        $event = 'A Grand Test';

        $webhook = new Webhook($url, $orderId, $name, $event);

        $this->assertEquals($url, $webhook->getUrl());
        $this->assertEquals($orderId, $webhook->getOrderId());
        $this->assertEquals($name, $webhook->getName());
        $this->assertEquals($event, $webhook->getEvent());

        $newUrl = 'http://bazbat.com';
        $newOrderId = 2;
        $newName = 'Webhook2';
        $newEvent = 'Another Grand Test';

        $webhook->setUrl($newUrl);
        $webhook->setOrderId($newOrderId);
        $webhook->setName($newName);
        $webhook->setEvent($newEvent);

        $this->assertEquals($newUrl, $webhook->getUrl());
        $this->assertEquals($newOrderId, $webhook->getOrderId());
        $this->assertEquals($newName, $webhook->getName());
        $this->assertEquals($newEvent, $webhook->getEvent());
    }
}

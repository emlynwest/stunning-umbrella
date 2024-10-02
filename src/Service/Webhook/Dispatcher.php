<?php

namespace App\Service\Webhook;

use App\Model\Webhook;

/**
 * Responsible for dispatching Webhook processing requests
 */
class Dispatcher
{
    public function __construct(protected Worker $worker){}

    /**
     * Accepts a number of Webhooks and queues them for processing
     * @param Webhook[] $webhooks
     * @return void
     */
    public function dispatch(array $webhooks): void
    {
        foreach ($webhooks as $webhook) {
            $this->worker->process($webhook);
        }
    }
}

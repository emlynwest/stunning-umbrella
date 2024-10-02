<?php

namespace App\Service\Webhook;

use App\Model\Webhook;

/**
 * Responsible for dispatching Webhook processing requests
 */
class Dispatcher
{
    protected int $endpointFailureLimit = 5;

    public function __construct(protected Worker $worker){}

    /**
     * Accepts a number of Webhooks and queues them for processing
     * @param Webhook[] $webhooks
     * @return void
     */
    public function dispatch(array $webhooks): void
    {
        $failedEndpointCount = [];

        foreach ($webhooks as $webhook) {
            if ($failedEndpointCount[$webhook->getUrl()] >= $this->endpointFailureLimit) {
                // TODO: Log out a failure because we hit the retry limit
                continue;
            }

            if (!$this->worker->process($webhook)) {
                $failedEndpointCount[$webhook->getUrl()] += 1;
            }
        }
    }
}

<?php

namespace App\Service\Webhook;

use App\Model\Webhook;
use Psr\Log\LoggerInterface;

/**
 * Responsible for dispatching Webhook processing requests
 */
class Dispatcher
{
    protected int $endpointFailureLimit = 5;

    public function __construct(
        protected Worker $worker,
        protected LoggerInterface $logger,
    ){}

    /**
     * Accepts a number of Webhooks and queues them for processing
     * @param Webhook[] $webhooks
     * @return void
     */
    public function dispatch(array $webhooks): void
    {
        $failedEndpointCount = [];

        foreach ($webhooks as $webhook) {
            if (($failedEndpointCount[$webhook->getUrl()] ?? 0) >= $this->endpointFailureLimit) {
                // Log out a failure because we hit the retry limit
                $this->logger->warning('Skipping ' . $webhook->getUrl() . ' due to reaching endpoint retry limit!');
                continue;
            }

            $this->logger->info('Processing webhook ' . $webhook->getUrl());

            if (!$this->worker->process($webhook)) {
                $failedEndpointCount[$webhook->getUrl()] = ($failedEndpointCount[$webhook->getUrl()] ?? 0) + 1;
            }
        }
    }
}

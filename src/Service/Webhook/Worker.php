<?php

namespace App\Service\Webhook;

use App\Model\Webhook;
use Carbon\Carbon;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Responsible for actually sending a webhook.
 * In the event of a non-200 status the call will be retired with an exponential backoff strategy until the limit is hit.
 */
class Worker
{
    /**
     * List of status codes that are considered to be successful
     * @var int[]
     */
    protected array $successCodes = [200, 201];

    /**
     * @param int $maxTimeout This is the maximum limit for any backoffs when retrying. Once this limit is hit the process
     *                        will be considered a failure.
     * @param float $backoffMultiplier Defines how much our backoff should be raised by. The current backoff will be
     *                                 multiplied with this value to calculate the new wait time. This should always be >1
     * @param int $initialBackoffTime Defines how long our initial backoff should be
     */
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected int $maxTimeout = 60,
        protected float $backoffMultiplier = 2,
        protected int $initialBackoffTime = 1,
    ){
        if ($this->backoffMultiplier <= 1) {
            throw new LogicException('Backoff multiplier must be greater than 1');
        }
    }

    /**
     * Attempts to send the given webhook, retrying on failures.
     * @param Webhook $webhook
     * @return void
     */
    public function process(Webhook $webhook): bool
    {
        $currentBackoff = $this->initialBackoffTime;

        $this->logger->info('Starting processing of ' . $webhook->getUrl());

        // Whilst current backoff < maxTimeout
        while($currentBackoff < $this->maxTimeout)
        {
            // Make the http request
            try{
                $result = $this->httpClient->request('GET', $webhook->getUrl());

                // If the request passes then return
                if (in_array($result->getStatusCode(), $this->successCodes)) {
                    $this->logger->info('Request successful');
                    return true;
                }
            } catch (TransportExceptionInterface $te) {
                // Do nothing, if we've thrown an exception we won't be returning a success on this loop and will want
                // to retry.
            }

            $this->logger->info('Request failed, sleeping for ' . $currentBackoff);

            // If the request fails then wait for the current backoff time
            // We're using Carbon::sleep() here because it provides us a nice way to mock the sleep() function in tests.
            Carbon::sleep($currentBackoff);

            // Increment the backoff time for the next go of the loop
            $currentBackoff *= $this->backoffMultiplier;
            $this->logger->info('Raising backoff time to ' . $currentBackoff . ' and retrying');
        }

        // Log that a request has reached a timeout
        $this->logger->info('Maximum backoff time reached, aborting');
        return false;
    }
}

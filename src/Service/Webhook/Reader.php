<?php

namespace App\Service\Webhook;

use App\Model\Webhook;

/**
 * This class is responsible for reading in the webhooks that need processing from a given file.
 * Currently this has no error handling or checking the columns of the CSV file. Ideally we'd want to do some validation
 * to make sure the input file is as expected and correctly throw errors if not.
 */
class Reader
{
    /**
     *
     * @param string $fileLocation Location of the webhook file
     * @return Webhook[]
     */
    public function loadWebhooks(string $fileLocation): array
    {
        // Open the file
        $file = fopen($fileLocation, 'r');

        // TODO: Ideally we should check that the fopen was successful and handle errors if not

        $webhooks = [];

        // Ignore the first line of the CSV file because we're expecting it to be headers
        fgetcsv($file);
        while(($data = fgetcsv($file)) !== false)
        {
            // For each entry turn it in to a new Webhook object
            $webhook = new Webhook(trim($data[0]), (int) trim($data[1]), trim($data[2]), trim($data[3]));
            $webhooks[] = $webhook;
        }

        return $webhooks;
    }
}

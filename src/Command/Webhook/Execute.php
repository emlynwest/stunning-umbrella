<?php

namespace App\Command\Webhook;

use App\Service\Webhook\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Execute extends Command
{
    public function __construct(protected Reader $reader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->
            setName('webhook:execute');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO: The actual file location should be provided via config
        $webhooks = $this->reader->loadWebhooks('/app/data/webhooks.txt');

        foreach ($webhooks as $webhook) {
            $output->writeln($webhook->getOrderId() . ' - ' . $webhook->getName());
        }

        return Command::SUCCESS;
    }
}

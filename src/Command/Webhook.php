<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Webhook extends Command
{
    protected function configure(): void
    {
        $this->
            setName('webhook:execute');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello world!');
        return Command::SUCCESS;
    }
}

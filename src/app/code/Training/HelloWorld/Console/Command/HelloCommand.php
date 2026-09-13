<?php

declare(strict_types=1);

namespace Training\HelloWorld\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('training:hello');
        $this->setDescription('Wypisuje pozdrowienie z modułu Training_HelloWorld.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello, Magento!');
        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace Training\PimSync\Console\Command;

use Magento\Framework\MessageQueue\PublisherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Training\PimSync\Api\Data\PimProductMessageInterfaceFactory;

/**
 * Symuluje zewnętrzny PIM publikujący wiadomości o produktach na RabbitMQ.
 * To jest "publisher" z Etapu 7.2 pkt 4 planu nauki.
 */
class SimulateCommand extends Command
{
    private const TOPIC = 'training.pim.product.sync';

    /**
     * Pula SKU istniejących w tym sklepie (sample data) — łatwo zweryfikować
     * efekt na produktach, które już znasz.
     */
    private const SKU_POOL = ['24-MB01', '24-MB03', '24-MB04', '24-MB05', '24-MB06'];

    public function __construct(
        private readonly PublisherInterface $publisher,
        private readonly PimProductMessageInterfaceFactory $messageFactory,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('training:pim:simulate');
        $this->setDescription('Symuluje zewnętrzny PIM publikujący wiadomości o produktach na RabbitMQ.');
        $this->addOption('count', null, InputOption::VALUE_OPTIONAL, 'Ile losowych wiadomości opublikować', 10);
        $this->addOption(
            'broken',
            null,
            InputOption::VALUE_NONE,
            'Dodatkowo opublikuj jedną celowo błędną wiadomość (test walidacji w konsumerze, Etap 7.2 pkt 5)'
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = (int) $input->getOption('count');

        foreach (range(1, $count) as $i) {
            $message = $this->messageFactory->create();
            $message->setSku(self::SKU_POOL[array_rand(self::SKU_POOL)]);
            $message->setName("PIM update {$message->getSku()}");
            $message->setPrice(rand(1000, 50000) / 100);
            $message->setQty(rand(0, 100));
            $message->setStatus(1);

            $this->publisher->publish(self::TOPIC, $message);
            $output->writeln("Published message with SKU {$message->getSku()}, price {$message->getPrice()}, qty {$message->getQty()}");
        }

        if ($input->getOption('broken')) {
            $message = $this->messageFactory->create();
            $message->setSku(self::SKU_POOL[array_rand(self::SKU_POOL)]);
            $message->setName("PIM update {$message->getSku()}");
            $message->setPrice(-10);
            $message->setQty(rand(0, 100));
            $message->setStatus(1);

            $this->publisher->publish(self::TOPIC, $message);
            $output->writeln("Published broken message with SKU {$message->getSku()}, price {$message->getPrice()}, qty {$message->getQty()}");
        }

        $output->writeln(sprintf('Opublikowano %d wiadomości na topic %s.', $count, self::TOPIC));

        return Command::SUCCESS;
    }
}

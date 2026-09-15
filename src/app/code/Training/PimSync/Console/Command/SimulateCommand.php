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

        // TODO:
        // 1. W pętli $count razy zbuduj losową wiadomość przez
        //    $this->messageFactory->create() i ustaw:
        //    - setSku() — losowy z self::SKU_POOL (np. array_rand());
        //    - setName() — cokolwiek sensownego, np. "PIM update {sku}";
        //    - setPrice() — losowa liczba, np. rand(1000, 50000) / 100;
        //    - setQty() — losowa liczba, np. rand(0, 100);
        //    - setStatus() — 1 (enabled).
        // 2. Opublikuj: $this->publisher->publish(self::TOPIC, $message).
        // 3. Wypisz przez $output->writeln(...), co dokładnie wysłałeś
        //    (SKU, cena, qty) — inaczej trudno będzie zweryfikować efekt.
        //
        // 4. Jeśli $input->getOption('broken') === true, opublikuj NA KOŃCU
        //    dodatkową wiadomość z celowo złymi danymi (np. price = -10 albo
        //    pusty sku = '') — to jest dane wejściowe do przetestowania
        //    walidacji, którą dopiszesz w PimProductSyncConsumer::process().

        $output->writeln(sprintf('Opublikowano %d wiadomości na topic %s.', $count, self::TOPIC));

        return Command::SUCCESS;
    }
}

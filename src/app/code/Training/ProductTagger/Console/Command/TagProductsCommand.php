<?php

declare(strict_types=1);

namespace Training\ProductTagger\Console\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagProductsCommand extends Command
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly State $appState,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('training:products:tag');
        $this->setDescription(
            'Oznacza atrybutem training_badge produkty z ceną >= podanej wartości.'
        );
        $this->addArgument(
            'price',
            InputArgument::REQUIRED,
            'Minimalna cena — produkty z price >= tej wartości dostaną badge'
        );
        $this->addArgument(
            'badge',
            InputArgument::OPTIONAL,
            'Wartość badge do ustawienia: Nowość, Promocja lub Hit',
            'Hit'
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
            // Area code już ustawiony (np. wywołanie w kontekście, gdzie ktoś
            // ustawił to wcześniej) — bezpiecznie ignorujemy.
        }

        $badge = $input->getArgument('badge');
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('price', (float) $input->getArgument('price'), 'gteq')
            ->create();

        $products = $this->productRepository->getList($searchCriteria)->getItems();
        if (!$products) {
            $output->writeln('Brak produktów spełniających warunek ceny.');

            return Command::SUCCESS;
        }

        $firstProduct = reset($products);
        $optionId = $firstProduct->getResource()
            ->getAttribute('training_badge')
            ->getSource()
            ->getOptionId($badge);

        // getOptionId() dla braku dopasowania potrafi zwrócić false, null albo
        // pusty string zależnie od source modelu — sprawdzamy "falsy" ogólnie,
        // bo prawidłowe option_id w tym atrybucie zawsze jest dodatnią liczbą.
        if (!$optionId) {
            $output->writeln(sprintf('<error>Nieznana wartość badge: "%s"</error>', $badge));

            return Command::FAILURE;
        }

        foreach ($products as $product) {
            $product->setData('training_badge', $optionId);
            $this->productRepository->save($product);
        }

        $output->writeln(sprintf('Otagowano %d produkt(ów) badge\'em: %s', count($products), $badge));

        return Command::SUCCESS;
    }
}

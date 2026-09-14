<?php

declare(strict_types=1);

namespace Training\ProductTagger\Console\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagProductsCommand extends Command
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
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
        // TODO:
        // 1. Zbuduj SearchCriteria filtrujące po cenie:
        //    $this->searchCriteriaBuilder
        //        ->addFilter('price', (float) $input->getArgument('price'), 'gteq')
        //        ->create();
        // 2. Pobierz produkty: $this->productRepository->getList($searchCriteria)->getItems().
        // 3. Dla atrybutu typu "select" wartość, którą zapisujesz przez setData(),
        //    to ID opcji, NIE tekst etykiety — znajdź, jak pobrać option_id po
        //    etykiecie z argumentu 'badge' (podpowiedź: atrybut ma getSource(),
        //    który ma metodę zwracającą opcje z ich ID — sprawdź
        //    \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource).
        // 4. Dla każdego produktu: $product->setData('training_badge', $optionId);
        //    $this->productRepository->save($product);
        // 5. Wypisz przez $output->writeln(...), ile produktów oznaczono i jakim
        //    badge'em.
        // 6. Zwróć Command::SUCCESS.
        //
        // Do przemyślenia i sprawdzenia (patrz Etap 3 w planie nauki): czy ta
        // komenda "widzi" produkt disabled albo z qty=0? Sprawdź eksperymentalnie
        // i zapisz sobie dlaczego tak/nie — to prosta droga do zrozumienia
        // różnicy między ProductRepositoryInterface::getList() a bezpośrednim
        // zapytaniem przez CollectionFactory.

        return Command::FAILURE;
    }
}

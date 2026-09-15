<?php

declare(strict_types=1);

namespace Training\Greeting\Api;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Zmapowany w etc/di.xml na generyczną klasę \Magento\Framework\Api\SearchResults
 * (standardowy wzorzec Magento Service Contracts).
 *
 * WAŻNE: getItems()/setItems() muszą być tu jawnie REDEKLAROWANE jako prawdziwe
 * metody z konkretnym typem w PHPDoc — sama adnotacja `@method` na poziomie
 * klasy (jak było wcześniej) nie wystarcza. Webapi (REST) buduje JSON dla
 * getList() przez refleksję PHP na METODACH — jeśli getItems() nie jest
 * przedeklarowane tutaj, refleksja trafia na generyczną deklarację z
 * SearchResultsInterface i nie wie, że elementy to GreetingInterface, przez co
 * każdy item w odpowiedzi REST wychodzi jako pusta tablica (zweryfikowane
 * empirycznie — dokładnie to się działo, dopóki tego nie dodałem). Ten sam
 * wzorzec widać w core, np. Magento\Catalog\Api\Data\ProductSearchResultsInterface.
 */
interface GreetingSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Training\Greeting\Api\Data\GreetingInterface[]
     */
    public function getItems();

    /**
     * @param \Training\Greeting\Api\Data\GreetingInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

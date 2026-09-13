<?php

declare(strict_types=1);

namespace Training\Greeting\Api;

use Magento\Framework\Api\SearchResultsInterface;
use Training\Greeting\Api\Data\GreetingInterface;

/**
 * Interfejs bez własnych metod — istnieje tylko po to, żeby SearchCriteria +
 * repozytorium miały własny, konkretny typ wyników zamiast generycznego
 * SearchResultsInterface. Zmapowany w etc/di.xml na generyczną klasę
 * \Magento\Framework\Api\SearchResults (standardowy wzorzec Magento Service
 * Contracts). @method docbloki tylko dla podpowiedzi IDE.
 *
 * @method GreetingInterface[] getItems()
 * @method $this setItems(GreetingInterface[] $items)
 */
interface GreetingSearchResultsInterface extends SearchResultsInterface
{
}

<?php

declare(strict_types=1);

namespace Training\Greeting\Model\ResourceModel\Greeting;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Training\Greeting\Model\Greeting as GreetingModel;
use Training\Greeting\Model\ResourceModel\Greeting as GreetingResource;

/**
 * Nadpisuje generyczny PHPDoc z Magento\Framework\Data\Collection (który
 * deklaruje getItems() jako \Magento\Framework\DataObject[]), żeby IDE i
 * narzędzia statycznej analizy wiedziały, że ta konkretna kolekcja realnie
 * zwraca instancje Greeting (zgodnie z _init() w _construct() poniżej).
 * Bez tego setItems(GreetingInterface[] $items) w GreetingRepository::getList()
 * wygląda dla IDE na niezgodność typów, mimo że w runtime jest poprawnie.
 *
 * @method GreetingModel[] getItems()
 */
class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(GreetingModel::class, GreetingResource::class);
    }
}

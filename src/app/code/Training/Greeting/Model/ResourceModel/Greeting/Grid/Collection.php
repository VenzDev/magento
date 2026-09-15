<?php

declare(strict_types=1);

namespace Training\Greeting\Model\ResourceModel\Greeting\Grid;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Training\Greeting\Model\ResourceModel\Greeting as GreetingResource;

/**
 * Osobna od zwykłej Collection (używanej przez GreetingRepository), z dwóch
 * powodów naraz:
 *
 * 1. Grid (UI Component listing) wymaga kolekcji implementującej
 *    \Magento\Framework\Api\Search\SearchResultInterface, żeby generyczny
 *    Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider mógł
 *    jej użyć (patrz mapowanie w etc/di.xml, CollectionFactory "collections").
 *
 * 2. Model encji to \Magento\Framework\View\Element\UiComponent\DataProvider\Document
 *    (generyczne DTO), NIE Training\Greeting\Model\Greeting — generyczny
 *    DataProvider::getData() woła $item->getCustomAttributes() na każdym
 *    wierszu przy budowaniu odpowiedzi dla grida, czego nasz zwykły model
 *    encji nie implementuje (rzuca "foreach() argument must be of type
 *    array|object, null given" — zweryfikowane empirycznie). Document
 *    opakowuje surowe kolumny SQL w coś, co tę metodę ma. Dokładnie tak samo
 *    robi to Magento\Cms\Model\ResourceModel\Block\Grid\Collection w core
 *    (tam $model też zostaje jako Document, nie jako encja Block).
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    protected function _construct(): void
    {
        $this->_init(Document::class, GreetingResource::class);
    }

    /**
     * @var AggregationInterface|null
     */
    private $aggregations;

    /**
     * @var SearchCriteriaInterface|null
     */
    private $searchCriteria;

    /**
     * @return AggregationInterface|null
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(?SearchCriteriaInterface $searchCriteria = null)
    {
        $this->searchCriteria = $searchCriteria;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Grid wylicza total count z getSize() dynamicznie — nie ma sensownego
     * "ustawiania" go z zewnątrz, więc to no-op (dokładnie jak w core).
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Grid ładuje itemy sam z bazy przez load() — nie mamy po co przyjmować
     * ich z zewnątrz, więc to też no-op (dokładnie jak w core).
     *
     * @param array|null $items
     * @return $this
     */
    public function setItems(?array $items = null)
    {
        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Training\HelloWorld\Block;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Training\Greeting\Api\Data\GreetingInterface;
use Training\Greeting\Api\GreetingRepositoryInterface;
use Training\Greeting\Model\Greeting as GreetingModel;

/**
 * Etap 14 (Full Page Cache) — blok pokazujący 3 ostatnie wpisy Greeting.
 *
 * Strona /helloworld jest cache'owana przez FPC. Dopóki getIdentities()
 * zwraca [], strona nie ma żadnego tagu powiązanego z tabelą training_greeting,
 * więc nowy wpis NIGDY jej nie unieważni (aż do wygaśnięcia TTL). Twoim
 * zadaniem jest to naprawić — TODO w getGreetings() i getIdentities() niżej,
 * oraz TODO w Training\Greeting\Model\Greeting::getIdentities().
 */
class LatestGreetings extends Template implements IdentityInterface
{
    private const LIMIT = 3;

    public function __construct(
        Context $context,
        private readonly GreetingRepositoryInterface $greetingRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly SortOrderBuilder $sortOrderBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * TODO:
     * 1. Zbuduj SortOrder malejąco po entity_id:
     *    $this->sortOrderBuilder->setField('entity_id')->setDescendingDirection()->create()
     * 2. Zbuduj SearchCriteria z limitem self::LIMIT i tym sortowaniem:
     *    $this->searchCriteriaBuilder->setPageSize(...)->addSortOrder(...)->create()
     * 3. Zwróć $this->greetingRepository->getList($criteria)->getItems().
     *
     * @return GreetingInterface[]
     */
    public function getGreetings(): array
    {
        $sortBuilder = $this->sortOrderBuilder->setField('entity_id')->setDescendingDirection()->create();
        $searchCriteria = $this->searchCriteriaBuilder->setPageSize(self::LIMIT)->addSortOrder($sortBuilder)->create();

        return $this->greetingRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Blok pokazuje listę, więc zależy od dowolnej zmiany w Greeting — tag
     * listy, który model emituje przy każdym zapisie i usunięciu. Tagi
     * per-wpis byłyby tu zbędne, bo tag listy już je pokrywa.
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [GreetingModel::CACHE_TAG];
    }
}

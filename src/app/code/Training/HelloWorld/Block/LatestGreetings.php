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
        return [];
    }

    /**
     * TODO: zwróć tagi cache, od których zależy ta strona. Efekt widać w
     * nagłówku odpowiedzi X-Magento-Tags (tryb developer).
     *
     * Do przemyślenia zanim napiszesz: blok pokazuje LISTĘ ostatnich wpisów.
     * Gdy ktoś doda NOWY wpis, dostaje on nowe ID, którego nie ma jeszcze w
     * żadnej scache'owanej stronie. Czy tagi per-wpis (np. training_greeting_5)
     * wystarczą, żeby taką stronę unieważnić? Jeśli nie — jaki tag musi
     * dostać i blok, i model, żeby się spotkały?
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [];
    }
}

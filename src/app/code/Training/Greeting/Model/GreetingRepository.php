<?php

declare(strict_types=1);

namespace Training\Greeting\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Training\Greeting\Api\Data\GreetingInterface;
use Training\Greeting\Api\GreetingRepositoryInterface;
use Training\Greeting\Api\GreetingSearchResultsInterfaceFactory;
use Training\Greeting\Model\ResourceModel\Greeting as GreetingResource;
use Training\Greeting\Model\ResourceModel\Greeting\CollectionFactory;

class GreetingRepository implements GreetingRepositoryInterface
{
    public function __construct(
        private readonly GreetingResource $resource,
        private readonly GreetingFactory $greetingFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly GreetingSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(GreetingInterface $greeting): GreetingInterface
    {
        // TODO: zapisz encję przez $this->resource->save($greeting) w
        // try/catch; przy wyjątku przerzuć jako CouldNotSaveException
        // (z poprzednim wyjątkiem jako $cause, żeby nie zgubić stack trace);
        // na końcu zwróć $greeting.
    }

    public function getById(int $id): GreetingInterface
    {
        // TODO:
        // 1. $greeting = $this->greetingFactory->create();
        // 2. $this->resource->load($greeting, $id);
        // 3. jeśli $greeting->getId() jest puste, rzuć
        //    NoSuchEntityException::singleField('entity_id', $id);
        // 4. zwróć $greeting.
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // TODO:
        // 1. $collection = $this->collectionFactory->create();
        // 2. $this->collectionProcessor->process($searchCriteria, $collection);
        // 3. $searchResults = $this->searchResultsFactory->create();
        // 4. $searchResults->setSearchCriteria($searchCriteria);
        // 5. $searchResults->setItems($collection->getItems());
        // 6. $searchResults->setTotalCount($collection->getSize());
        // 7. zwróć $searchResults.
    }

    public function delete(GreetingInterface $greeting): bool
    {
        // TODO: $this->resource->delete($greeting) w try/catch; przy wyjątku
        // rzuć CouldNotDeleteException; na końcu zwróć true.
    }

    public function deleteById(int $id): bool
    {
        // TODO: return $this->delete($this->getById($id));
    }
}

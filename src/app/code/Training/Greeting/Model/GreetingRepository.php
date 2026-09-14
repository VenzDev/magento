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
        try {
            $this->resource->save($greeting);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save greeting'),
                $e
            );
        }

        return $greeting;
    }

    public function getById(int $id): GreetingInterface
    {
        $greeting = $this->greetingFactory->create();
        $this->resource->load($greeting, $id);

        if ($greeting->getId() === null) {
            throw new NoSuchEntityException(
                __('Could not find greeting with ID %1', $id)
            );
        }

        return $greeting;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    public function delete(GreetingInterface $greeting): bool
    {
        try {
            $this->resource->delete($greeting);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete greeting'),
                $e
            );
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }
}

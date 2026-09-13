<?php

declare(strict_types=1);

namespace Training\Greeting\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Training\Greeting\Api\Data\GreetingInterface;

interface GreetingRepositoryInterface
{
    /**
     * @throws CouldNotSaveException
     */
    public function save(GreetingInterface $greeting): GreetingInterface;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $id): GreetingInterface;

    /**
     * Uwaga: CELOWO bez natywnego return type na tej metodzie interfejsu.
     * Implementacja (GreetingRepository) zwraca instancję generycznej klasy
     * \Magento\Framework\Api\SearchResults (zob. preference w etc/di.xml) —
     * ta klasa NIE deklaruje "implements GreetingSearchResultsInterface" na
     * poziomie PHP, więc natywny return type `: GreetingSearchResultsInterface`
     * tutaj wywaliłby TypeError w runtime. To świadomy, standardowy wzorzec w
     * core Magento dla Service Contracts opartych o SearchResults — dlatego
     * PHPDoc, nie native typing.
     *
     * @return GreetingSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(GreetingInterface $greeting): bool;

    /**
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id): bool;
}

<?php

declare(strict_types=1);

namespace Training\Greeting\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Training\Greeting\Api\GreetingRepositoryInterface;

/**
 * Resolver dla Query.trainingGreetings (etc/schema.graphqls). Analogicznie do
 * GreetingCountIndexer/TagProductsCommand — DI już wpięte, logika (zbudowanie
 * SearchCriteria z argumentów paginacji i zmapowanie wyniku na tablicę zgodną
 * ze schematem GraphQL) zostaje do dopisania.
 */
class GreetingsResolver implements ResolverInterface
{
    public function __construct(
        private readonly GreetingRepositoryInterface $greetingRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @param array<string, mixed> $args GraphQL zawsze przekazuje argumenty
     *        zapytania jako tablicę — tu znajdziesz 'pageSize' i 'currentPage'
     *        zdefiniowane w schema.graphqls.
     *
     * @return array{total_count: int, items: array<int, array<string, mixed>>}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {

        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize((int) ($args['pageSize'] ?? 20))
            ->setCurrentPage((int) ($args['currentPage'] ?? 1))
            ->create();

        $greetings = $this->greetingRepository->getList($searchCriteria);

        $mappedItems = [];
        foreach ($greetings->getItems() as $item) {
            $mappedItems[] = [
                'entity_id' => $item->getId(),
                'message' => $item->getMessage(),
                'product_id' => $item->getProductId(),
                'created_at' => $item->getCreatedAt(),
            ];
        }

        return [
            'total_count' => $greetings->getTotalCount(),
            'items' => $mappedItems,
        ];
    }
}

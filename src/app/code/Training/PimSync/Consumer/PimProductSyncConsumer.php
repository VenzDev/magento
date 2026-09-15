<?php

declare(strict_types=1);

namespace Training\PimSync\Consumer;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Psr\Log\LoggerInterface;
use Training\PimSync\Api\Data\PimProductMessageInterface;

/**
 * Odbiorca wiadomości z kolejki training.pim.product.sync.queue (patrz
 * etc/queue_consumer.xml). Symuluje aktualizację katalogu na podstawie
 * "danych z zewnętrznego PIM-a".
 */
class PimProductSyncConsumer
{
    private const DEFAULT_ATTRIBUTE_SET_ID = 4;
    private const DEFAULT_WEBSITE_ID = 1;
    private const DEFAULT_SOURCE_CODE = 'default';

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductInterfaceFactory $productFactory,
        private readonly SourceItemsSaveInterface $sourceItemsSave,
        private readonly SourceItemInterfaceFactory $sourceItemFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function process(PimProductMessageInterface $message): void
    {
        $this->validate($message);

        try {
            $product = $this->productRepository->get($message->getSku());
            $isNew = false;
        } catch (NoSuchEntityException $e) {
            $product = $this->productFactory->create();
            $isNew = true;
        }

        if ($isNew) {
            $product->setSku($message->getSku());
            $product->setAttributeSetId(self::DEFAULT_ATTRIBUTE_SET_ID);
            $product->setTypeId('simple');
            $product->setVisibility(Visibility::VISIBILITY_BOTH);
            $product->setWebsiteIds([self::DEFAULT_WEBSITE_ID]);
        }

        $product->setName($message->getName());
        $product->setPrice($message->getPrice());
        $product->setStatus($message->getStatus());

        $this->productRepository->save($product);

        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSku($message->getSku());
        $sourceItem->setSourceCode(self::DEFAULT_SOURCE_CODE);
        $sourceItem->setQuantity((float) $message->getQty());
        $sourceItem->setStatus($message->getQty() > 0 ? 1 : 0);
        $this->sourceItemsSave->execute([$sourceItem]);

        $this->logger->info(sprintf(
            '%s produkt sku=%s name="%s" price=%.2f qty=%d status=%d',
            $isNew ? 'Utworzono' : 'Zaktualizowano',
            $message->getSku(),
            $message->getName(),
            $message->getPrice(),
            $message->getQty(),
            $message->getStatus()
        ));
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validate(PimProductMessageInterface $message): void
    {
        if ($message->getSku() === '') {
            throw new \InvalidArgumentException('PIM message has an empty SKU.');
        }

        if ($message->getPrice() < 0) {
            throw new \InvalidArgumentException(sprintf(
                'PIM message for sku=%s has a negative price (%.2f).',
                $message->getSku(),
                $message->getPrice()
            ));
        }

        if ($message->getQty() < 0) {
            throw new \InvalidArgumentException(sprintf(
                'PIM message for sku=%s has a negative qty (%d).',
                $message->getSku(),
                $message->getQty()
            ));
        }
    }
}

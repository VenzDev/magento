<?php

declare(strict_types=1);

namespace Training\PimSync\Model;

use Training\PimSync\Api\Data\PimProductMessageInterface;

/**
 * Wydzielone z PimProductSyncConsumer (Etap 8) — czysta logika, zero
 * zależności od Magento/DI, więc łatwo testowalna unit testem bez mockowania
 * repozytoriów/loggera.
 */
class PimProductMessageValidator
{
    /**
     * @throws \InvalidArgumentException
     */
    public function validate(PimProductMessageInterface $message): void
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

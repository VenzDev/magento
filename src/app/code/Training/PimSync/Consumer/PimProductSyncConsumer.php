<?php

declare(strict_types=1);

namespace Training\PimSync\Consumer;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
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
        // TODO (Etap 7.2 pkt 5 — walidacja):
        // Sprawdź $message->getSku()/getPrice()/getQty() i rzuć wyjątek (np.
        // \InvalidArgumentException) dla danych, które nie mają sensu (pusty
        // SKU, cena < 0, qty < 0) — ZANIM zaczniesz cokolwiek zapisywać.
        // Po zaimplementowaniu: opublikuj celowo złą wiadomość
        // (bin/magento training:pim:simulate --broken) i sprawdź w RabbitMQ
        // Management UI (zakładka kolejki training.pim.product.sync.queue),
        // co się z nią dzieje — wraca do kolejki (redelivery w pętli) czy
        // znika? To odpowiedź na pytanie o acknowledgement z Etapu 7.1.

        try {
            $product = $this->productRepository->get($message->getSku());
            $isNew = false;
        } catch (NoSuchEntityException $e) {
            $product = $this->productFactory->create();
            $isNew = true;
        }

        // TODO (Etap 7.2 pkt 3 — logika synchronizacji):
        // 1. Jeśli $isNew — ustaw minimalny komplet danych wymagany do
        //    utworzenia prostego produktu: setSku($message->getSku()),
        //    setName($message->getName()), setAttributeSetId(4) (Default —
        //    ten sam ID co w Etapie 3), setTypeId('simple'),
        //    setVisibility(4) (Catalog, Search), setWebsiteIds([1]).
        //    Jeśli produkt już istnieje — zaktualizuj tylko name/status.
        // 2. $product->setPrice($message->getPrice());
        // 3. $product->setStatus($message->getStatus());
        // 4. $this->productRepository->save($product);
        // 5. Zaktualizuj stan magazynowy PRZEZ MSI (ten sklep ma włączone
        //    Magento_InventoryApi — NIE używaj starego StockRegistryInterface):
        //      $sourceItem = $this->sourceItemFactory->create();
        //      $sourceItem->setSku($message->getSku());
        //      $sourceItem->setSourceCode('default');
        //      $sourceItem->setQuantity((float) $message->getQty());
        //      $sourceItem->setStatus($message->getQty() > 0 ? 1 : 0);
        //      $this->sourceItemsSave->execute([$sourceItem]);
        // 6. Zaloguj wynik: $this->logger->info(...) — leci do osobnego pliku
        //    var/log/pim_sync.log (kanał skonfigurowany w etc/di.xml), nie do
        //    ogólnego system.log.
    }
}

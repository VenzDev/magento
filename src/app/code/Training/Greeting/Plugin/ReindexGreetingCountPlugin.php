<?php

declare(strict_types=1);

namespace Training\Greeting\Plugin;

use Magento\Framework\Indexer\IndexerRegistry;
use Training\Greeting\Api\Data\GreetingInterface;
use Training\Greeting\Api\GreetingRepositoryInterface;
use Training\Greeting\Model\Indexer\GreetingCountIndexer;

/**
 * Bez tego pluginu tryb "Update on Save" dla training_greeting_count jest
 * czysto deklaratywny i nic nie robi automatycznie — zweryfikowane
 * empirycznie przy Etapie 4 (zapis produktu z trybem "Update on Save"
 * ustawionym nie aktualizował training_greeting_count, dopóki nie odpalono
 * ręcznie `indexer:reindex`). etc/mview.xml daje wyzwalacz SQL tylko dla
 * trybu "Update by Schedule" (przetwarzanego przez cron) — dla realtime
 * trzeba jawnie wywołać reindexRow() po zapisie, tak jak robi to core
 * Magento (np. Magento\Catalog\Model\Indexer\Product\Price\Plugin\Product).
 */
class ReindexGreetingCountPlugin
{
    public function __construct(
        private readonly IndexerRegistry $indexerRegistry
    ) {
    }

    public function afterSave(
        GreetingRepositoryInterface $subject,
        GreetingInterface $result
    ): GreetingInterface {
        $productId = $result->getProductId();

        if (!$productId) {
            // 0 to sentinel "brak powiązanego produktu" (product_id jest
            // NOT NULL DEFAULT 0 od backfillu — zob. Setup/Patch/Data/
            // BackfillGreetingProductId.php) — nic nie indeksujemy.
            return $result;
        }

        $indexer = $this->indexerRegistry->get(GreetingCountIndexer::INDEXER_ID);

        // W trybie "Update by Schedule" trigger SQL (etc/mview.xml) już
        // zapisał zmianę do tabeli changelog — cron ją przetworzy. Ręczne
        // reindexRow() tutaj byłoby zbędne i sprzeczne z ideą przetwarzania
        // wsadowego (cel trybu "Schedule" to właśnie NIE robić tego
        // synchronicznie przy każdym zapisie).
        if (!$indexer->isScheduled()) {
            $indexer->reindexRow((int) $productId);
        }

        return $result;
    }
}

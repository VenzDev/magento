<?php

declare(strict_types=1);

namespace Training\Greeting\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

/**
 * Buduje tabelę training_greeting_count (product_id, greeting_count) na
 * podstawie tabeli training_greeting. Implementuje DWA interfejsy:
 * - Indexer\ActionInterface — wymagany zawsze (tryb "Update on Save" +
 *   ręczne `indexer:reindex`).
 * - Mview\ActionInterface — wymagany tylko dla trybu "Update by Schedule"
 *   (patrz etc/mview.xml); Magento woła execute() z listy ID zebranych
 *   z tabeli changelog między uruchomieniami crona.
 */
class GreetingCountIndexer implements IndexerActionInterface, MviewActionInterface
{
    private const GREETING_TABLE = 'training_greeting';
    private const INDEX_TABLE = 'training_greeting_count';

    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * Pełny rebuild — wywoływane przez `indexer:reindex training_greeting_count`
     * albo gdy indekser jest oznaczony jako "Reindex required".
     *
     * TODO:
     * 1. Weź połączenie: $connection = $this->resourceConnection->getConnection();
     * 2. Wyczyść całą tabelę docelową: $connection->truncateTable($this->resourceConnection->getTableName(self::INDEX_TABLE));
     * 3. Zbuduj SELECT grupujący training_greeting po product_id z COUNT(*),
     *    z warunkiem WHERE product_id IS NOT NULL.
     * 4. Wstaw wynik do training_greeting_count przez
     *    $connection->query($connection->insertFromSelect($select, $indexTable, ['product_id', 'greeting_count']));
     */
    public function executeFull(): void
    {
    }

    /**
     * Częściowy rebuild dla konkretnych product_id — wywoływane przez tryb
     * "Update by Schedule" (przez execute()) oraz executeRow().
     *
     * TODO:
     * 1. Dla każdego $id z $ids policz COUNT(*) z training_greeting WHERE product_id = $id.
     * 2. Zrób "upsert" do training_greeting_count: jeśli wiersz dla product_id
     *    istnieje — UPDATE greeting_count; jeśli nie — INSERT. Podpowiedź:
     *    $connection->insertOnDuplicate($indexTable, [...], ['greeting_count']).
     * 3. Jeśli count wynosi 0 (produkt nie ma już żadnych greetingów), możesz
     *    usunąć wiersz z indeksu zamiast trzymać 0 — przemyśl, co ma sens.
     *
     * @param int[] $ids
     */
    public function executeList(array $ids): void
    {
    }

    /**
     * Reindex pojedynczego ID — wywoływane w trybie "Update on Save" zaraz po
     * zapisie encji, którą ten indekser obserwuje.
     */
    public function executeRow($id): void
    {
        $this->executeList([$id]);
    }

    /**
     * Wymagane przez Mview\ActionInterface — wywoływane przez cron w trybie
     * "Update by Schedule" z listą ID zebranych z tabeli changelog od
     * ostatniego przebiegu.
     *
     * @param int[] $ids
     */
    public function execute($ids): void
    {
        $this->executeList($ids);
    }
}

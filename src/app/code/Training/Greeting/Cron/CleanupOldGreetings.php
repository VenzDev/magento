<?php

declare(strict_types=1);

namespace Training\Greeting\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Job crona: training_greeting_cleanup (patrz etc/crontab.xml).
 * Usuwa wpisy z training_greeting starsze niż konfigurowalna liczba dni
 * (Stores > Configuration > Training Greeting > General > Retention,
 * patrz etc/adminhtml/system.xml i domyślna wartość w etc/config.xml).
 */
class CleanupOldGreetings
{
    private const XML_PATH_RETENTION_DAYS = 'training_greeting/general/retention_days';
    private const GREETING_TABLE = 'training_greeting';

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * TODO:
     * 1. Odczytaj liczbę dni retencji:
     *    $this->scopeConfig->getValue(self::XML_PATH_RETENTION_DAYS, ScopeInterface::SCOPE_STORE)
     *    — obsłuż sensownie brak wartości / wartość <= 0 (nie usuwaj wtedy nic,
     *    zaloguj ostrzeżenie zamiast rzucać wyjątkiem — to job cykliczny, nie
     *    może wywalić się na złej konfiguracji).
     * 2. Wylicz próg czasowy (teraz minus N dni), np. przez
     *    (new \DateTime())->modify("-{$days} days")->format('Y-m-d H:i:s').
     * 3. Zbuduj i wykonaj DELETE na training_greeting WHERE created_at < próg:
     *    $connection = $this->resourceConnection->getConnection();
     *    $table = $this->resourceConnection->getTableName(self::GREETING_TABLE);
     *    $connection->delete($table, $connection->quoteInto('created_at < ?', $threshold));
     * 4. delete() zwraca liczbę usuniętych wierszy — zaloguj to przez
     *    $this->logger->info(...) razem z użytym progiem retencji.
     * 5. Do przemyślenia (nie musisz implementować): czy po usunięciu starych
     *    greetingów powinieneś odświeżyć training_greeting_count z Etapu 4?
     *    Ten indeks trzyma tylko COUNT per product_id, więc usunięcie starych
     *    wierszy może go rozsynchronizować — jak (i czy w ogóle warto) to
     *    naprawić w tym jobie?
     */
    public function execute(): void
    {
        $retention = (int) $this->scopeConfig->getValue(self::XML_PATH_RETENTION_DAYS, ScopeInterface::SCOPE_STORE);

        if ($retention <= 0) {
            $this->logger->warning('Retention days must be greater than 0, skipping cleanup');
            return;
        }

        $threshold = (new \DateTime('now', new \DateTimeZone('UTC')))
            ->modify("-{$retention} days")
            ->format('Y-m-d H:i:s');

        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName(self::GREETING_TABLE);
        $rows = $connection->delete($table, $connection->quoteInto('created_at < ?', $threshold));

        $this->logger->info("Deleted {$rows} rows older than {$threshold}");
    }
}

<?php

declare(strict_types=1);

namespace Training\Greeting\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Musi się wykonać PRZED zmianą training_greeting.product_id na NOT NULL w
 * db_schema.xml. Declarative schema aplikuje zmiany schematu i dopiero potem
 * data patche w JEDNYM przebiegu setup:upgrade — więc nie da się w tym samym
 * wydaniu jednocześnie zmienić kolumny na NOT NULL i backfillować NULL-e tym
 * samym patchem: schema zostałaby zaaplikowana pierwsza i ALTER TABLE od razu
 * by się wywalił na istniejących NULL-ach. Stąd dwa kroki na dwóch osobnych
 * przebiegach setup:upgrade (patrz historia commitów tego pliku i db_schema.xml).
 */
class BackfillGreetingProductId implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('training_greeting'),
            ['product_id' => 0],
            ['product_id IS NULL']
        );
    }

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}

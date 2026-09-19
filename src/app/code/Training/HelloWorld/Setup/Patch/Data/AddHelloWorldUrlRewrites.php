<?php

declare(strict_types=1);

namespace Training\HelloWorld\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * Etap 13 (URL rewrites) — dopisuje dwa ręczne wpisy (entity_type="custom")
 * do tabeli url_rewrite, oba celujące w kontroler /helloworld:
 * - "witaj" — cichy rewrite (redirect_type=0): URL w pasku przeglądarki
 *   zostaje /witaj, odpowiedź to zwykłe 200.
 * - "stare-hello" — przekierowanie 301: przeglądarka dostaje Location i
 *   sama idzie na /helloworld.
 *
 * Patch jest jednorazowy (odnotowany w patch_list po setup:upgrade), więc
 * zmiana tych wpisów później wymaga albo nowego patcha, albo usunięcia
 * wiersza z patch_list i ponownego setup:upgrade.
 *
 * Oba wpisy MUSZĄ lecieć w jednym replace(), bo dzielą tę samą tożsamość
 * encji (entity_type + entity_id + store_id). replace() najpierw kasuje
 * wszystkie rewrite'y danej encji, a potem wstawia to, co dostał — więc
 * osobne replace([$silent]) skasowałoby po cichu "stare-hello"
 * (zweryfikowane empirycznie). Konflikt rzuca UrlAlreadyExistsException
 * dopiero wtedy, gdy o ten sam request_path+store_id bije się INNA encja.
 */
class AddHelloWorldUrlRewrites implements DataPatchInterface
{
    private const ENTITY_TYPE = 'custom';

    /**
     * Celowo sam frontName, nie pełne "helloworld/index/index" (choć oba
     * poprawnie routują do tego samego kontrolera przez domyślne
     * index/index). Przy redirect_type != 0 Magento wstawia target_path
     * wprost w nagłówek Location, więc pełna ścieżka wyciekłaby userowi i
     * to ona zostałaby w pasku przeglądarki.
     */
    private const TARGET_PATH = 'helloworld';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly UrlPersistInterface $urlPersist,
        private readonly UrlRewriteFactory $urlRewriteFactory,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $storeId = (int) $this->storeManager->getStore()->getId();

        $silent = $this->urlRewriteFactory->create();
        $silent->setEntityType(self::ENTITY_TYPE)
            ->setEntityId(0)
            ->setRequestPath('witaj')
            ->setTargetPath(self::TARGET_PATH)
            ->setRedirectType(0)
            ->setStoreId($storeId);

        $permanent = $this->urlRewriteFactory->create();
        $permanent->setEntityType(self::ENTITY_TYPE)
            ->setEntityId(0)
            ->setRequestPath('stare-hello')
            ->setTargetPath(self::TARGET_PATH)
            ->setRedirectType(OptionProvider::PERMANENT)
            ->setStoreId($storeId);

        $this->urlPersist->replace([$silent, $permanent]);

        $this->moduleDataSetup->getConnection()->endSetup();
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

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
 * Etap 13 (URL rewrites) — TODO w apply():
 *
 * Zbuduj dwa obiekty UrlRewrite przez $this->urlRewriteFactory->create() i
 * zapisz je razem przez $this->urlPersist->replace([$rewrite1, $rewrite2]):
 *
 * 1. "Cichy" rewrite (bez przekierowania — URL w pasku przeglądarki się nie
 *    zmienia, tylko routing pod spodem trafia gdzie indziej):
 *    request_path='witaj', target_path='helloworld', entity_type='custom',
 *    entity_id=0, redirect_type=0,
 *    store_id=$this->storeManager->getStore()->getId().
 * 2. Przekierowanie 301: request_path='stare-hello', target_path='helloworld',
 *    entity_type='custom', entity_id=0,
 *    redirect_type=OptionProvider::PERMANENT, ten sam store_id co wyżej.
 *
 * Setery na UrlRewrite (Magento\UrlRewrite\Service\V1\Data\UrlRewrite) to
 * fluent API: ->setEntityType(...)->setEntityId(...)->setRequestPath(...)
 * ->setTargetPath(...)->setRedirectType(...)->setStoreId(...).
 *
 * WAŻNE zanim uruchomisz `bin/magento setup:upgrade`: dopóki apply() jest
 * pustą metodą (TODO), setup:upgrade i tak oznaczy ten patch jako "już
 * zastosowany" w tabeli patch_list — bo Magento nie wie, że w środku nic
 * się nie wykonało. Jeśli teraz odpalisz setup:upgrade, a dopiero potem
 * uzupełnisz TODO, kolejne setup:upgrade GO POMINIE (bo jest już w
 * patch_list) i Twoja logika nigdy się nie wykona. Najpierw uzupełnij TODO,
 * dopiero potem `bin/magento setup:upgrade`. Jeśli już się pomyliłeś:
 * `DELETE FROM patch_list WHERE patch_name LIKE '%AddHelloWorldUrlRewrites%'`
 * i uruchom setup:upgrade jeszcze raz.
 *
 * Do przemyślenia (nie musisz kodować): co się stanie, jeśli spróbujesz
 * zapisać drugi rewrite z takim samym request_path+store_id jak istniejący?
 * (unique constraint URL_REWRITE_REQUEST_PATH_STORE_ID w tabeli
 * url_rewrite) — sprawdź jaki wyjątek rzuca UrlPersistInterface::replace().
 */
class AddHelloWorldUrlRewrites implements DataPatchInterface
{
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

        // TODO: patrz opis klasy wyżej.

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

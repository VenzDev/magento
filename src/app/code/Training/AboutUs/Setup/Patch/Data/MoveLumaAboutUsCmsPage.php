<?php

declare(strict_types=1);

namespace Training\AboutUs\Setup\Patch\Data;

use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Strona CMS "About us" z sample data Luma ma url_rewrite "about-us", a router
 * UrlRewrite (sortOrder 20) działa przed routerem standardowym (30) — bez tej
 * zmiany CMS przechwytywałby /about-us zamiast kontrolera tego modułu.
 *
 * Zmiana identifier przez resource model odpala cms_page_save_after, którego
 * observer z Magento_CmsUrlRewrite sam przepisuje url_rewrite.
 */
class MoveLumaAboutUsCmsPage implements DataPatchInterface
{
    private const OLD_IDENTIFIER = 'about-us';
    private const NEW_IDENTIFIER = 'about-us-luma';

    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly PageResource $pageResource
    ) {
    }

    public function apply(): self
    {
        $page = $this->pageFactory->create();
        $this->pageResource->load($page, self::OLD_IDENTIFIER, 'identifier');

        if ($page->getId()) {
            $page->setIdentifier(self::NEW_IDENTIFIER);
            $this->pageResource->save($page);
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}

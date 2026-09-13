<?php

declare(strict_types=1);

namespace Training\Greeting\Plugin;

use Magento\Catalog\Model\Product;

class ProductNamePlugin
{
    /**
     * Plugin `after` — $subject to oryginalny obiekt Product, $result to
     * wartość już zwrócona przez natywne getName(). W plugin `after` zawsze
     * modyfikujesz i zwracasz $result (nigdy $subject).
     *
     * TODO: dopisz widoczny sufiks do nazwy, np. `return $result . ' 👋';`
     */
    public function afterGetName(Product $subject, $result)
    {
    }
}

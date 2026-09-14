<?php

declare(strict_types=1);

namespace Training\ProductTagger\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddProductBadgeAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'training_badge';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'label' => 'Training Badge',
                'input' => 'select',
                'source' => Table::class,
                'required' => false,
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General',
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_filterable' => true,
                'used_in_product_listing' => true,
                'option' => [
                    'values' => [
                        'Nowość',
                        'Promocja',
                        'Hit',
                    ],
                ],
            ]
        );

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSetId);

        $eavSetup->addAttributeToSet(
            Product::ENTITY,
            $attributeSetId,
            $attributeGroupId,
            self::ATTRIBUTE_CODE
        );

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

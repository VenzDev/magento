<?php

declare(strict_types=1);

namespace Training\AccountConfirmation\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddConfirmationRequestedAtAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'confirmation_requested_at';

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
            Customer::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type' => 'datetime',
                'label' => 'Confirmation Requested At',
                'input' => 'date',
                'required' => false,
                'visible' => false,
                'user_defined' => true,
                'system' => false,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
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

<?php

namespace CJ\PointRedemption\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;

class AddRedemptionAttributes implements DataPatchInterface, PatchVersionInterface
{

    const IS_POINT_REDEEMABLE_ATTRIBUTE_CODE = 'is_point_redeemable';
    const POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE = 'point_redemption_amount';
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory          $eavSetupFactory,
        QuoteSetupFactory        $quoteSetupFactory,
        SalesSetupFactory        $salesSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE,
            [
                'group' => 'General',
                'backend' => '',
                'frontend' => '',
                'label' => 'Is Point Redeemable',
                'type' => 'int',
                'input' => 'boolean',
                'source' => Boolean::class,
                'default' => Boolean::VALUE_NO,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'apply_to' => '',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE,
            [
                'group' => 'General',
                'backend' => '',
                'frontend' => '',
                'label' => 'Redemption Point',
                'type' => 'int',
                'input' => 'text',
                'class' => 'validate-not-negative-number',
                'default' => 0,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'apply_to' => '',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $quoteSetup->addAttribute('quote_item', self::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE, ['type' => 'int']);
        $quoteSetup->addAttribute('quote_item', self::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE, ['type' => 'int']);

        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $salesSetup->addAttribute('order_item', self::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE, ['type' => 'int']);
        $salesSetup->addAttribute('order_item', self::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE, ['type' => 'int']);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}

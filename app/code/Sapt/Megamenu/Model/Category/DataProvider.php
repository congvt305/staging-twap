<?php

namespace Sapt\Megamenu\Model\Category;

use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Catalog\Model\Category\Image;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Config\DataInterfaceFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        Registry $registry,
        Config $eavConfig,
        RequestInterface $request,
        CategoryFactory $categoryFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null,
        ?AuthorizationInterface $auth = null,
        ?ArrayUtils $arrayUtils = null,
        ScopeOverriddenValue $scopeOverriddenValue = null,
        ArrayManager $arrayManager = null,
        FileInfo $fileInfo = null,
        ?Image $categoryImage = null,
        ?DataInterfaceFactory $uiConfigFactory = null
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $eavValidationRules, $categoryCollectionFactory, $storeManager, $registry, $eavConfig, $request, $categoryFactory, $meta, $data, $pool, $auth, $arrayUtils, $scopeOverriddenValue, $arrayManager, $fileInfo, $categoryImage, $uiConfigFactory);
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        if ($this->storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return parent::getFieldsMap();
        }

        return [
            'general' =>
                [
                    'parent',
                    'path',
                    'is_active',
                    'include_in_menu',
                    'name',
                ],
            'content' =>
                [
                    'image',
                    'description',
                    'landing_page',
                ],
            'display_settings' =>
                [
                    'display_mode',
                    'is_anchor',
                    'available_sort_by',
                    'use_config.available_sort_by',
                    'default_sort_by',
                    'use_config.default_sort_by',
                    'filter_price_range',
                    'use_config.filter_price_range',
                ],
            'search_engine_optimization' =>
                [
                    'url_key',
                    'url_key_create_redirect',
                    'use_default.url_key',
                    'url_key_group',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                ],
            'assign_products' =>
                [
                ],
            'design' =>
                [
                    'custom_use_parent_settings',
                    'custom_apply_to_products',
                    'custom_design',
                    'page_layout',
                    'custom_layout_update',
                ],
            'schedule_design_update' =>
                [
                    'custom_design_from',
                    'custom_design_to',
                ],
            'sapt-menu' =>
                [
                    'sapt_menu_hide_item',
                    'sapt_menu_type',
                    'sapt_menu_static_width',
                    'sapt_menu_cat_columns',
                    'sapt_menu_float_type',
                    'sapt_menu_cat_label',
                    'sapt_menu_icon_img',
                    'sapt_menu_font_icon',
                    'sapt_menu_block_top_content',
                    'sapt_menu_block_left_width',
                    'sapt_menu_block_left_content',
                    'sapt_menu_block_right_width',
                    'sapt_menu_block_right_content',
                    'sapt_menu_block_bottom_content',
                ],
            'category_view_optimization' =>
                [
                ],
            'category_permissions' =>
                [
                ],
        ];
    }
}

<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Model\Source;

use Eguana\Faq\Helper\Data;
use Magento\Backend\Model\Auth\Session as SessionAlias;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;
use Magento\Store\Model\StoresConfig as StoresConfigAlias;

/**
 * Class Type
 */
class Category implements OptionSourceInterface
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var SessionAlias
     */
    private $adminSession;
    /**
     * @var StoresConfigAlias
     */
    private $storesConfig;
    /**
     * @var StoreManagerInterfaceAlias
     */
    private $storeManager;

    /**
     * Category constructor.
     * @param Data $helper
     * @param SessionAlias $adminSession
     * @param StoresConfigAlias $storesConfig
     * @param StoreManagerInterfaceAlias $storeManager
     */
    public function __construct(
        Data $helper,
        SessionAlias $adminSession,
        StoresConfigAlias $storesConfig,
        StoreManagerInterfaceAlias $storeManager
    ) {
        $this->helper = $helper;
        $this->adminSession = $adminSession;
        $this->storesConfig = $storesConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $storeId = $this->storeManager->getStore()->getId();

        $categories = $this->helper->getFaqTypes();
        $categoriesWithValue = [];

        $index = 1;
        foreach ($categories as $category) {
            $categoriesWithValue[$storeId . $index] = $category;
            $index++;
        }

        return $categoriesWithValue;
    }

    /**
     * getFaqCategoryList method
     * @return array
     */
    public function getFaqCategoryList()
    {
        $faqTypes = $this->helper->getFaqTypes();
        $typeArray = [];

        if ($faqTypes == null) {
            return $typeArray;
        } else {
            $i = 1;
            foreach ($faqTypes as $key => $label) {
                if (!$label == null) {
                    $array = ['value' => $i, 'label'=>$label];
                    $typeArray[] = $array;
                }
                $i++;
            }
            return $typeArray;
        }
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->adminSession->isLoggedIn()) {
            $adminUserRoleCheck = $this->adminSession->getUser()->getRole()->getData('gws_is_all');

            $storeCategoryList = [];
            $categoryList = [];

            if ($adminUserRoleCheck == true) {
                $configValueCategories = $this->storesConfig->getStoresConfigByPath('faq/category');
                // $key is store id and value is list of categories set in configuration
                foreach ($configValueCategories as $key => $value) {
                    if ($key == 0 || $value == null) {
                        continue;
                    }

                    $index = 1;
                    foreach ($value as $category) {
                        $categoryList[$key][] = [
                            'label' => $category,
                            'value' => $key . $index
                        ];
                        $index++;
                    }
                    $storeCategoryList[$key] = [
                        'label'   => $this->storeManager->getStore($key)->getName(),
                        'value'   => $categoryList[$key]
                    ];
                }
                return $storeCategoryList;
            } else {
                $adminScopeStores = $this->adminSession->getUser()->getRole()->getData('gws_stores');

                if (count($adminScopeStores) == 1) {
                    $configValueCategories = $this->helper->getStoreCategories($adminScopeStores[0]);
                    $index = 1;
                    foreach ($configValueCategories as $category) {
                        $categoryList[] = [
                            'label' => $category,
                            'value' => $adminScopeStores[0] . $index
                        ];
                        $index++;
                    }
                    $storeCategoryList[$adminScopeStores[0]] = [
                        'label'   => $this->storeManager->getStore($adminScopeStores[0])->getName(),
                        'value'   => $categoryList
                    ];
                } else {
                    foreach ($adminScopeStores as $storeId) {
                        $categories = $this->helper->getStoreCategories($storeId);
                        $index = 1;
                        foreach ($categories as $category) {
                            $categoryList[$storeId][] = [
                                'label' => $category,
                                'value' => $storeId . $index
                            ];
                            $index++;
                        }
                    }
                    foreach ($categoryList as $key => $value) {
                        if ($key == 0) {
                            continue;
                        }
                        $storeCategoryList[$key] = [
                            'label'   => $this->storeManager->getStore($key)->getName(),
                            'value'   => $value
                        ];
                    }
                }
                return $storeCategoryList;
            }
        }
    }

    /**
     * categoryNameToSearch method
     * @param $categoryId
     * @return mixed|string
     */
    public function categoryNameToSearch($categoryId)
    {
        $categoryName ='';

        foreach ($this->toOptionArray() as $option) {
            if ($option['value'] == $categoryId) {
                $categoryName = $option['label'];
            }
        }

        return $categoryName;
    }
}

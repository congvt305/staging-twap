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
use Eguana\Faq\Model\FaqConfiguration\FaqConfiguration;
use Magento\Backend\Model\Auth\Session as SessionAlias;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Serialize\Serializer\Json;
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
     * @var FaqConfiguration
     */
    private $faqConfiguration;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Data $helper
     * @param SessionAlias $adminSession
     * @param StoresConfigAlias $storesConfig
     * @param StoreManagerInterfaceAlias $storeManager
     * @param FaqConfiguration $faqConfiguration
     * @param Json $json
     */
    public function __construct(
        Data $helper,
        SessionAlias $adminSession,
        StoresConfigAlias $storesConfig,
        StoreManagerInterfaceAlias $storeManager,
        FaqConfiguration $faqConfiguration,
        Json $json
    ) {
        $this->helper = $helper;
        $this->adminSession = $adminSession;
        $this->storesConfig = $storesConfig;
        $this->storeManager = $storeManager;
        $this->faqConfiguration = $faqConfiguration;
        $this->json = $json;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $storeId = $this->storeManager->getStore()->getId();

        $categories = $this->faqConfiguration->getCategory($storeId);
        $categoriesWithValue = [];

        $index = 0;
        foreach ($categories as $category) {
            $categoriesWithValue[$storeId . '.' . $index] = $category;
            $index++;
        }
        if ($this->helper->getFaqSortOrder()) {
            arsort($categoriesWithValue);
        } else {
            asort($categoriesWithValue);
        }
        return $categoriesWithValue;
    }

    /**
     * getFaqCategoryList method
     * @return array
     */
    public function getFaqCategoryList()
    {
        $faqTypes = $this->faqConfiguration->getCategory($storeId);
        $typeArray = [];

        if ($faqTypes == null) {
            return $typeArray;
        } else {
            $i = 0;
            foreach ($faqTypes as $key => $label) {
                if (!$label == null) {
                    $array = ['value' => $i, 'label' => $label];
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
        $storeCategoryList = [];
        $categoryList = [];
        $configValueCategories = $this->storesConfig->getStoresConfigByPath('faq/category/categories');
        // $key is store id and value is list of categories set in configuration
        foreach ($configValueCategories as $key => $value) {
            if ($key == 0 || $value == null || $value == []) {
                continue;
            }

            $index = 0;
            $value = $this->json->unserialize($value);
            foreach ($value as $category) {
                $categoryList[$key][] = [
                    'label' => $category,
                    'value' => $key . '.' . $index
                ];
                $index++;
            }
            $storeCategoryList[$key] = [
                'label'   => $this->storeManager->getStore($key)->getName(),
                'value'   => $categoryList[$key]
            ];
        }
        return $storeCategoryList;
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

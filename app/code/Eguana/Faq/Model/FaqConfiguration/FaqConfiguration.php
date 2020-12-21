<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 14/12/20
 * Time: 8:40 PM
 */
declare(strict_types=1);

namespace Eguana\Faq\Model\FaqConfiguration;

use Eguana\Faq\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * To get configuration values from admin configuration
 *
 * Class FaqConfiguration
 */
class FaqConfiguration
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Json $json
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $json,
        StoreManagerInterface $storeManager,
        Data $dataHelper,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    /**
     * To get configuration value of categoriies on basis of store
     *
     * @param $storeId
     * @return array
     */
    public function getCategory($storeId) : array
    {
        $result = $this->dataHelper->getStoreCategories($storeId);
        $category = [];
        if (isset($result)) {
            $categories = $this->json->unserialize($result);
            foreach ($categories as $value) {
                $category[] = $value['attribute_name'];
            }
        }
        return $category;
    }

    /**
     * To get configuration categories values for form field
     *
     * @param $storeId
     * @return string
     */
    public function getCategoryValue($storeId) : string
    {
        $category = '';
        try {
            if ($storeId[0] == 0) {
                $storeManagerDataList = $this->storeManager->getStores();
                $storeId[] = [];
                foreach ($storeManagerDataList as $key => $value) {
                    $storeId[] = $key;
                }
            }

            $i = 0;
            foreach ($storeId as $store) {
                $result = $this->dataHelper->getStoreCategories($store);
                $storename = $this->storeManager->getStore($store)->getName();
                $category .= '<option style="font-weight: bold;" disabled data-title="' . $storename. '">'
                            . $storename . '</option>';
                $i++;
                if (!empty($result)) {
                    $categories = $this->json->unserialize($result);
                    if (count($categories) > 0) {
                        $index = 0;
                        foreach ($categories as $value) {
                            $category .= '<option data-title="' . $value['attribute_name'] . '" value="'
                                . $store . '.' . $index . '">'
                                . $value['attribute_name'] . '</option>';
                            $index++;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $category;
    }
}

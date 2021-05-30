<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 27/5/21
 * Time: 5:50 PM
 */
namespace Eguana\Directory\Plugin\Model;

use Magento\Directory\Model\Currency as CurrencyAlias;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;

/**
 * This class is consists of before method which is responsible for removing decimal zero
 * for product price for vietnam site
 * Class Currency
 */
class Currency
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Http
     */
    private $request;

    /**
     * Currency constructor.
     * @param StoreManagerInterface $storeManager
     * @param Http $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Http $request
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * this before plugin is used to set the precision value zero
     * @param CurrencyAlias $subject
     * @param $price
     * @param $precision
     * @param array $options
     * @param bool $includeContainer
     * @param false $addBrackets
     * @return array
     */
    public function beforeFormatPrecision(
        CurrencyAlias $subject,
        $price,
        $precision,
        $options = [],
        $includeContainer = true,
        $addBrackets = false
    ) {
        $fullActionName = $this->request->getFullActionName();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        if ($websiteId == "8" || $fullActionName == "mui_index_render") {
            $precision = 0;
            return [$price, $precision, $options, $includeContainer, $addBrackets];
        }
    }
}

<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 24/5/21
 * Time: 11:17 AM
 */
namespace Eguana\CustomCheckout\Plugin\Model;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Cart
 *
 * This class is consists of before plugin which is used to
 * redirect at checkout page after direct purchase
 */
class Cart
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Cart constructor.
     * @param UrlInterface $url
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface $url,
        Http $request,
        StoreManagerInterface $storeManager
    ) {
        $this->url = $url;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * This method is used to skip the cart page if the store is vietnam
     * @param $subject
     * @param $productInfo
     * @param null $requestInfo
     * @return array
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        $storeId = $this->getStoreId();
        $checkoutUrl = $this->storeManager->getStore()->getBaseUrl()."checkout/";
        if ((isset($requestInfo['return_url']) && $requestInfo['return_url'] != '') && ($storeId == "8")) {
            $accUrl = $this->url->getUrl($checkoutUrl);
            $this->request->setParam('return_url', $accUrl);
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * Get store identifier
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}

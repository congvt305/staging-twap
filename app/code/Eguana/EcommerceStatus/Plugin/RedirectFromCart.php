<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: saba
 * Date: 6/25/20
 * Time: 1:31 PM
 */
namespace Eguana\EcommerceStatus\Plugin;

use Magento\Checkout\Controller\Cart\Index as CartIndex;
use Magento\Framework\App\Response\Http as responseHttp;
use Magento\Framework\UrlInterface;
use Eguana\EcommerceStatus\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Redirect form cart page to home page
 * Class RedirectToHome
 * @package Eguana\EcommerceStatus\Plugin
 */

class RedirectFromCart
{

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var responseHttp
     */
    private $response;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * RedirectFromCart constructor.
     * @param responseHttp $response
     * @param UrlInterface $url
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        responseHttp $response,
        UrlInterface $url,
        Data $helperData,
        StoreManagerInterface $storeManager
    )
    {
        $this->response = $response;
        $this->url = $url;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
    }

    /**
     * Redirect form checkout page to home page if Ecommerce Switched is disable
     * @param CartIndex $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(CartIndex $subject, $result)
    {
        if (!$this->helperData->getECommerceStatus()) {
            $url = $this->storeManager->getStore()->getBaseUrl();
            $this->response->setRedirect($url);
        }
        return $result;
    }
}


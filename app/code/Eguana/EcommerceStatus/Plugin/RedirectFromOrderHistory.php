<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: mobeen
 * Date: 07/14/20
 * Time: 06:50 PM
 */
namespace Eguana\EcommerceStatus\Plugin;

use Magento\Sales\Controller\Order\History as OrderHistory;
use Magento\Framework\App\Response\Http as responseHttp;
use Magento\Framework\UrlInterface;
use Eguana\EcommerceStatus\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Redirect form Order History page to home page
 * Class RedirectFromOrderHistory
 */
class RedirectFromOrderHistory
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
     * RedirectFromCheckout constructor.
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
     * Redirect form order history page to home page if Ecommerce Switched is disable
     * @param OrderHistory $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(OrderHistory $subject, $result)
    {
        if (!$this->helperData->getECommerceStatus()) {
            $url = $this->storeManager->getStore()->getBaseUrl();
            $this->response->setRedirect($url);
        }
        return $result;
    }
}

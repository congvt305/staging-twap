<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hoolah\Hoolah\Model\Checkout;

use Hoolah\Hoolah\Controller\Main as HoolahMain;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const CODE = 'hoolah';
    
    protected $objectManager;
    protected $extSettings;
    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Hoolah\Hoolah\Helper\ExtSettings $extSettings,
        \Magento\Checkout\Model\Session $checkoutSession
    ){
        $this->objectManager = $objectManager;
        $this->extSettings = $extSettings;
        $this->checkoutSession = $checkoutSession;
    }
    
    public function getViewFileUrl($fileId, array $params = [])
    {
        $assetRepository = $this->objectManager->create('\Magento\Framework\View\Asset\Repository');
        $request = $this->objectManager->create('\Magento\Framework\App\RequestInterface');
        $params = array_merge(['_secure' => $request->isSecure()], $params);
        return $assetRepository->getUrlWithParams($fileId, $params);
    }
    
    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $is_available = true;
    
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        
        // in 2.4 we had some bug with disabled hoolah (it was solving by cache clearing or pages several reopening)
        // in 2.3 too (one case) - so totally disable it
        //if (version_compare($productMetadata->getVersion(), '2.4', '<'))
        //{
        //    $quote = $this->checkoutSession->getQuote();
        //
        //    $billingAddress = $quote->getBillingAddress();
        //    $billing = $billingAddress->getData();
        //    
        //    if (!HoolahMain::check_country($this->extSettings->gatewayEnabledCountries(HoolahMain::get_countries()), $billing['country_id']))
        //        $is_available = false;
        //    
        //    $total = floatval($quote->getGrandTotal());
        //    
        //    //hard-coded max
        //    if (($billing['country_id'] == 'SG' && $total > 3000) ||
        //        ($billing['country_id'] == 'MY' && $total > 9000))
        //        $is_available = false;
        //    
        //    if ($this->extSettings->gatewayEnabledMinAmount() && $this->extSettings->gatewayEnabledMinAmount() > $total)
        //        $is_available = false;
        //    
        //    if ($this->extSettings->gatewayEnabledMaxAmount() && $this->extSettings->gatewayEnabledMaxAmount() < $total)
        //        $is_available = false;
        //}
        
        return [
            'payment' => [
                self::CODE => [
                    'paymentAcceptanceMarkSrc' => 'https://cdn.hoolah.co/images/ShopBack_logo_big_spark-20220706-031305.svg',
                    'paymentDescription' => $this->extSettings->gatewayDescription(),
                    'disabled' => !$is_available
                ]
            ]
        ];
    }
}

<?php

namespace CJ\SFLocker\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\DirectoryDataProcessor as CoreDirectoryDataProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Directory data processor.
 *
 * This class adds various country and region dictionaries to checkout page.
 * This data can be used by other UI components during checkout flow.
 */
class DirectoryDataProcessor
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterProcess(CoreDirectoryDataProcessor $sub, $result, $jsLayout)
    {
        if ($this->_storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            if (isset($result['components']['checkoutProvider']['dictionaries']['region_id'])) {
                $regionIds = $result['components']['checkoutProvider']['dictionaries']['region_id'];
                $newRegionIds = [];
                $ignores = ['New Territories', 'Kowloon', 'Hong Kong Island', 'China'];
                $enableMacau = $this->_scopeConfig->getValue(
                    'carriers/vlogic/enable_macau',
                    ScopeInterface::SCOPE_STORE,
                    $this->_storeManager->getStore()->getId()
                );
                if (!$enableMacau) {
                    $ignores[] = 'Macau';
                }
                foreach ($regionIds as $regionId) {
                    if (!in_array($regionId['title'], $ignores)) {
                        $newRegionIds[] = $regionId;
                    }
                }
                $result['components']['checkoutProvider']['dictionaries']['region_id'] = $newRegionIds;
            }
        }

        return $result;
    }

}

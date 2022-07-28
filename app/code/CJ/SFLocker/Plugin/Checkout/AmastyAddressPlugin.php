<?php

namespace CJ\SFLocker\Plugin\Checkout;

use Amasty\CheckoutCore\Helper\Address;
use Amasty\CheckoutCore\Model\Config;
use Amasty\CheckoutCore\Model\Field;
use Magento\Directory\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class AmastyAddressPlugin
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';

    /**
     * @var Field
     */
    protected $fieldSingleton;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $directoryData;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Field $fieldSingleton
     * @param Data $directoryData
     * @param Config $configProvider
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Field $fieldSingleton,
        Data $directoryData,
        Config $configProvider
    ) {
        $this->fieldSingleton = $fieldSingleton;
        $this->storeManager = $storeManager;
        $this->directoryData = $directoryData;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Address $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return void
     */
    public function aroundFillEmpty(
        Address $subject,
        callable $proceed,
        \Magento\Quote\Model\Quote\Address $address
    ) {
        if ($this->storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return $proceed($address);
        }
        try {
            if (!$this->configProvider->isEnabled()) {
                return;
            }

            $fieldConfig = $this->fieldSingleton->getConfig(
                $this->storeManager->getStore()->getId()
            );

            $requiredFields = [
                'firstname',
                'lastname',
                'street',
                'city',
                'telephone',
                'postcode',
                'country_id',
            ];

            foreach ($requiredFields as $code) {
                if (!isset($fieldConfig[$code])) {
                    continue;
                }

                /** @var \Amasty\CheckoutCore\Model\Field $field */
                $field = $fieldConfig[$code];
                if (((!$address->hasData($code) || $address->getData($code) === 0) && !$field->getData('enabled'))
                    ||
                    ($address->hasData($code) && !$address->getData($code) && !$field->getData('required'))
                ) {
                    $defaultValue = '-';

                    switch ($code) {
                        case 'country_id':
                            $defaultValue = $this->configProvider->getDefaultCountryId();
                            break;
                        case 'telephone':
                            $defaultValue = '';
                            break;
                        case 'region_id':
                            if ($this->directoryData->isRegionRequired($address->getCountryId())) {
                                $defaultValue = $this->configProvider->getDefaultRegionId($address);
                            } else {
                                continue 2;
                            }
                            break;
                    }
                    $address->setData($code, $defaultValue);
                }
            }
        } catch (\Exception $e) {
            return $proceed($address);
        }
    }

}

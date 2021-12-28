<?php

namespace Eguana\Directory\Plugin\Controller\Address;

use Magento\Store\Model\StoreManagerInterface;

class FormPost
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    protected $storeCode = 'vn_laneige';

    /**
     * ShippingInformationManagement constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function beforeExecute(\Magento\Customer\Controller\Address\FormPost $formPost) {
        $storeCode = $this->storeManager->getStore()->getCode();
        if ($storeCode !== $this->storeCode) {
            $fields = ['city_id','ward_id', 'ward'];
            foreach ($fields as $field) {
                $formPost->getRequest()->setPostValue($field, null);
            }
        }
    }

}

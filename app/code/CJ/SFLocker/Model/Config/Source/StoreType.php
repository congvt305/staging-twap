<?php

namespace CJ\SFLocker\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class StoreType implements OptionSourceInterface
{
    const AP_STORE = '1';
    const SF_LOCKER = '2';
    const SF_STORE = '3';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::AP_STORE, 'label' => __('AP Store')],
            ['value' => self::SF_LOCKER, 'label' => __('SF Locker')],
            ['value' => self::SF_STORE, 'label' => __('SF Store')]
        ];
    }
}

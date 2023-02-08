<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/
namespace Amasty\Birth\Model\Source;

class Group extends \Magento\Customer\Model\Customer\Attribute\Source\Group
    implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift(
            $options,
            [
                'value' => 0,
                'label' => __('NOT LOGEED IN')
            ]
        );

        return $options;
    }
}

<?php
declare(strict_types=1);

namespace Sapt\Catalog\Plugin\Catalog\Model;

class Config
{
    /**
     * Add new option
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param $options
     * @return mixed
     */
    public function afterGetAttributeUsedForSortByArray(
        \Magento\Catalog\Model\Config $catalogConfig,
        $options
    ){
        $options['new'] = __('up to date');
        $options['low_to_high'] = __('Price - Low To High');
        $options['high_to_low'] = __('Price - High To Low');
        $options['created_at'] = __('Creation date');
        return $options;
    }
}

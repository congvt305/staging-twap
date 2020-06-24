<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-03
 * Time: 오후 5:55
 */

namespace Amore\Sap\Model\Source;

class UrlType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => \Amore\Sap\Model\Connection\Request::URL_TYPE_DEV,
                'label' => __('Dev'),
            ],
            [
                'value' => \Amore\Sap\Model\Connection\Request::URL_TYPE_STG,
                'label' => __('Staging'),
            ],
            [
                'value' => \Amore\Sap\Model\Connection\Request::URL_TYPE_PRD,
                'label' => __('Production'),
            ],
        ];
    }
}

<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-09-15
 * Time: 오후 4:41
 */

namespace Ecpay\Ecpaypayment\Model\Config\Source;

class IssueAllowanceNotify implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
          ["value" => "S", 'label' => __("SMS")],
          ["value" => "E", 'label' => __("Email")],
          ["value" => "A", 'label' => __("All Methods")],
          ["value" => "N", 'label' => __("Do not Notify")],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            "S" => __('SMS'), "E" => __('Email'), "A" => __("All Method"), "N" => __("Do not Notify")
        ];
    }
}

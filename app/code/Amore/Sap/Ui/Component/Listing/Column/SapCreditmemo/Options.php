<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-05
 * Time: 오후 4:43
 */

namespace Amore\Sap\Ui\Component\Listing\Column\SapCreditmemo;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    const WAITING_FOR_SAP_RESPONSE = 0;

    const SUCCESS_TO_SAP = 1;

    /**
     * @var array
     */
    protected $options;

    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];

            foreach ($this->getSapSendCheckStatusList() as $key => $value) {
                $this->options[] = [
                    'value' => $key,
                    'label' => $value
                ];
            }
        }
        return $this->options;
    }

    public function getSapSendCheckStatusList()
    {
        return [
            self::WAITING_FOR_SAP_RESPONSE => __("Waiting for SAP Response."),
            self::SUCCESS_TO_SAP => __("Success")
        ];
    }
}

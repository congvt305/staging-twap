<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-10
 * Time: 오후 5:39
 */

namespace Amore\Sap\Ui\Component\Listing\Column\SapRma;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    const ERROR_BEFORE_SEND = 0;

    const SUCCESS_TO_SAP = 1;

    const FAIL_TO_SAP = 2;

    const RESEND_SUCCESS = 3;

    /**
     * @var array
     */
    protected $options;

    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];

            foreach ($this->getSapSendCheckStatusList() as $key => $value) {
                $this->options[$key] = $value;
            }
        }
        return $this->options;
    }

    public function getSapSendCheckStatusList()
    {
        return [
            self::ERROR_BEFORE_SEND => __("Error Before Send"),
            self::SUCCESS_TO_SAP => __("Success"),
            self::FAIL_TO_SAP => __("Fail"),
            self::RESEND_SUCCESS => __("Resend Success")
        ];
    }
}

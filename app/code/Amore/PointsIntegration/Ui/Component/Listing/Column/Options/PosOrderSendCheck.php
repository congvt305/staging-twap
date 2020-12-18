<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-18
 * Time: 오전 10:20
 */

namespace Amore\PointsIntegration\Ui\Component\Listing\Column\Options;

use Magento\Framework\Data\OptionSourceInterface;

class PosOrderSendCheck implements OptionSourceInterface
{
    const NOT_SENT_TO_POS = 0;

    const SENT_TO_POS = 1;

    /**
     * @var array
     */
    protected $options;

    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];

            foreach ($this->getOrderSendToPosStatusList() as $key => $value) {
                $this->options[] = [
                    'value' => $key,
                    'label' => $value
                ];
            }
        }
        return $this->options;
    }

    public function getOrderSendToPosStatusList()
    {
        return [
          self::NOT_SENT_TO_POS => __("not sent"),
          self::SENT_TO_POS => __("sent")
        ];
    }
}

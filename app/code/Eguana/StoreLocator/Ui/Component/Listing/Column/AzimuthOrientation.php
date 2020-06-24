<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-02
 * Time: 오후 4:31
 */

namespace Eguana\StoreLocator\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Eguana\StoreLocator\Helper\ConfigData;

/**
 * Class for azimuth orientation in form
 *
 * Class AzimuthOrientation
 *  Eguana\StoreLocator\Ui\Component\Listing\Column
 */
class AzimuthOrientation implements OptionSourceInterface
{
    /**
     * @var ConfigData
     */
    protected $_configDataHelper;

    protected $_options;

    /**
     * AzimuthOrientation constructor.
     * @param ConfigData $configDataHelper
     */
    public function __construct(ConfigData $configDataHelper)
    {
        $this->_configDataHelper = $configDataHelper;
    }

    /**
     * Source model for field
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options == null) {
            $optionValue = $this->getOptionValue();
            $this->_options[] = [
                'value' => '',
                'label' => __('select...')
            ];
            foreach ($optionValue as $value => $label) {

                $this->_options[] = [
                    'value' => $value,
                    'label' => $label
                ];
            }
        }
        return $this->_options;
    }

    /**
     * Helper function for getting option values
     * @return array
     */
    private function getOptionValue()
    {
        $dataString = $this->_configDataHelper->getAzimuthOrientation();
        $dataSplits = explode(',', $dataString);
        $dataArray = [];
        foreach ($dataSplits as $dataLabel) {
            $key = str_replace(' ', '_', mb_strtolower($dataLabel));
            $value = $dataLabel;
            $dataArray[$key] = $value;
        }
        return$dataArray;
    }
}

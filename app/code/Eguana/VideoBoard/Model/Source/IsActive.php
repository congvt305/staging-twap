<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/6/20
 * Time: 2:57 PM
 */
namespace Eguana\VideoBoard\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class to convert labels on admin panel
 *
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @const STATUS_ENABLED
     */
    const STATUS_ENABLED = 1;

    /**
     * @const STATUS_DISABLED
     */
    const STATUS_DISABLED = 0;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}

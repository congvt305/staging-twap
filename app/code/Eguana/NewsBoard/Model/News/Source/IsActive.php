<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 2:00 PM
 */
declare(strict_types=1);

namespace Eguana\NewsBoard\Model\News\Source;

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
    public function toOptionArray() : array
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
     * get status
     *
     * @return array
     */
    private function getAvailableStatuses() : array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}

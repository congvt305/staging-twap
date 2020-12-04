<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/10/20
 * Time: 6:50 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model\Event\Source;

use Eguana\EventReservation\Model\Event;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * For values of store view in event listing
 *
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @param Event $event
     */
    public function __construct(
        Event $event
    ) {
        $this->event = $event;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $availableOptions = $this->event->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}

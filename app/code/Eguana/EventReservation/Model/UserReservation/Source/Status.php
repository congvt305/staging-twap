<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 26/10/20
 * Time: 3:50 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model\UserReservation\Source;

use Eguana\EventReservation\Model\UserReservation;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * For values of statuses
 *
 * Class Status
 */
class Status implements OptionSourceInterface
{
    /**
     * @var UserReservation
     */
    private $userReservation;

    /**
     * @param UserReservation $userReservation
     */
    public function __construct(
        UserReservation $userReservation
    ) {
        $this->userReservation = $userReservation;
    }

    /**
     * Get status options
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $availableOptions = $this->userReservation->getAvailableStatuses();
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

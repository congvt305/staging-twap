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
 * For option values of agreement
 *
 * Class Agreement
 */
class Agreement implements OptionSourceInterface
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
     * Get agreement options
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $agreementOptions = $this->userReservation->getAgreementOptions();
        $options = [];
        foreach ($agreementOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}

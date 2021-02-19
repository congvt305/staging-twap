<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: ali
 * Date: 19/2/21
 * Time: 4:00 PM
 */
declare(strict_types=1);

namespace Eguana\FacebookPixel\ViewModel;

use Eguana\FacebookPixel\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

/**
 * This ViewModel is used for Facebook pixel
 *
 * Class FacebookPixel
 */
class FacebookPixel implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Is Redemption Confirm
     *
     * @return bool
     */
    public function isRedemptionConfirm()
    {
        $result = $this->helper->isRedemptionConfirm();
        return $result ? true : false;
    }
}

<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 19
 * Time: 오후 5:00
 */

namespace Amore\CustomerRegistration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * It will use for the pos step during registration
 * Class POS
 * @package Amore\CustomerRegistration\ViewModel
 */
class POS implements ArgumentInterface
{


    /**
     * @var \Amore\CustomerRegistration\Helper\Data
     */
    private $configHelper;


    public function __construct(\Amore\CustomerRegistration\Helper\Data $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * Return the cms block identifier
     * This function will get the cms block identifer set by the admin
     * in the configuration against the terms for POS.
     * @return string
     */
    public function getTermsCmsBlockId()
    {
        return $this->configHelper->getTermsCMSBlockId();
    }
}
<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 12/12/19
 * Time: 12:38 PM
 */

namespace Eguana\StoreLocator\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Eguana\StoreLocator\Helper\ConfigData;

/**
 * class for get API parameter from admin panel
 * Class jsLoader
 *  Eguana\StoreLocator\Block\Adminhtml
 */
class JsLoader extends Template
{
    /**
     * @var ConfigData
     */
    private $confighelper;

    /**
     * JsLoader constructor.
     * @param ConfigData $helper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ConfigData $helper,
        Context $context,
        array $data = []
    ) {
        $this->confighelper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * get api key from admin config
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->confighelper->getApiKey();
    }
}

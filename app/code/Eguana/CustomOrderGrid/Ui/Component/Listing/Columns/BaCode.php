<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/12/20
 * Time: 4:00 PM
 */
namespace Eguana\CustomOrderGrid\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Amore\CustomerRegistration\Helper\Data;

/**
 * Show/Hide Ba Code column on bases of configuration
 *
 * Class BaCode
 */
class BaCode extends Column
{
    /**
     * @var Data
     */
    private $customerRegistrationHelper;

    /**
     * @param Data $customerRegistrationHelper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Data $customerRegistrationHelper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->customerRegistrationHelper = $customerRegistrationHelper;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->customerRegistrationHelper->getBaCodeEnable()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}

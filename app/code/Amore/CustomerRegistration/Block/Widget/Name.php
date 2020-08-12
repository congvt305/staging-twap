<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/8/20
 * Time: 8:50 PM
 */
namespace Amore\CustomerRegistration\Block\Widget;

use Magento\Customer\Block\Widget\Name as CustomerName;

class Name extends CustomerName
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('Amore_CustomerRegistration::widget/name.phtml');
    }
}

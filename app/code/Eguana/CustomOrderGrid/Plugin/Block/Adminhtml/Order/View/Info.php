<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 3/9/20
 * Time: 1:28 PM
 */

namespace Eguana\CustomOrderGrid\Plugin\Block\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\View\Info as InfoAlias;

/**
 * This class for after plugin of method getOrderStoreName
 * Class Info
 */
class Info
{
    /**
     * After Plugin For getOrderStoreName
     * This plugin is used to remove the Store View Name from the order info in Admin Panel
     * @param InfoAlias $subject
     * @param $result
     * @return string
     */
    public function afterGetOrderStoreName(InfoAlias $subject, $result)
    {
        if ($result) {
            $resultArray = explode('<br/>', $result);
            array_pop($resultArray);
            $result = implode('<br/>', $resultArray);
        }
        return $result;
    }
}

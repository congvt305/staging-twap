<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 8/9/20
 * Time: 4:29 PM
 */
namespace Eguana\NameSorter\Plugin\Model\Order\Email\Container;

use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\Order\Email\Container\Template as TemplateAlias;

/**
 * This class is used for the before pligun which swap
 * the First and Last Name for transaction email vars
 *
 * Class Template
 */
class Template
{
    /**
     * @var Http
     */
    private $request;

    /**
     * Template constructor.
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Set email template variables
     * before plugin
     * This method is use to swap the First and Last Name for transaction email vars
     * @param array $vars
     * @return void
     */
    public function beforeSetTemplateVars(TemplateAlias $subject, array $vars)
    {
        $order = $vars['order'];
        $orderData  = $order->getData();
        $controllerName   = $this->request->getControllerName();
        $isGuest    = $order->getData('customer_is_guest');
        $entityType  = $order->getEntityType();
        if (($isGuest == '1' && $controllerName == 'order_invoice' && $entityType == 'invoice')
            || ($isGuest == '1' && $entityType == 'order')
            || ($isGuest == '1' && $entityType == 'shipment')
            || ($isGuest == '1' && $entityType == 'order_edit')) {
            return [$vars];
        }
        $firstname  = $order->getData('customer_firstname');
        $lastname   = $order->getData('customer_lastname');
        $orderData  = $vars['order_data'];
        $orderData['customer_name'] = $lastname . ' ' . $firstname;
        $vars['order_data'] = $orderData;
        return [$vars];
    }
}

<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * Date: 16/7/20
 * Time: 4:26 PM
 */
namespace Eguana\CustomRMA\Plugin;

use Magento\Rma\Controller\Returns\Submit;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Eguana\CustomRMA\Model\RmaConfiguration;
use Magento\Eav\Api\AttributeRepositoryInterface;


/**
 * This class create custom rma
 *
 * Class RmaManagement
 * @package Eguana\CustomRMA\Plugin
 */
class RmaManagement
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RmaConfiguration
     */
    private $rmaConfiguration;

    /**
     * RmaManagement constructor.
     * @param RequestInterface $request
     * @param RmaConfiguration $rmaConfiguration
     */

    public function __construct(
        RequestInterface $request,
        RmaConfiguration $rmaConfiguration
)
    {
        $this->request = $request;
        $this->rmaConfiguration = $rmaConfiguration;
    }

    /**
     * Resetting the post
     * @param Submit $subject
     */
    public function beforeExecute(Submit $subject)
    {
        if ($this->rmaConfiguration->isRmaActive()) {
            $orderId = (int)$this->request->getParam('order_id');
            $customer_custom_email = $this->request->getParam('customer_custom_email') ?: '';
            $rma_comment = $this->request->getParam('rma_comment') ?: '';
            $form_key = $this->request->getParam('form_key');
            $shippingPreference = $subject->getRequest()->getParam('shipping_preference');

            $resolution = $this->rmaConfiguration->getRmaResolution();
            $condition = $this->rmaConfiguration->getRmaCondition();
            $reason = $this->rmaConfiguration->getRmaReason();
            $reason_other = ($reason == 'other')? $this->rmaConfiguration->getRmaReasonOther():'' ;

            $postOrderDetails['customer_custom_email'] = $customer_custom_email;

            if ($this->request->getParam('orderDetails')) {
                $orderDetails = explode(',', $this->request->getParam('orderDetails'));

                foreach ($orderDetails as $key => $item) {
                    $orderItem = explode('||', $item);
                    $order_item_id =  $orderItem[0];
                    $qty_requested = $orderItem[1] ;
                    $postOrderDetails['items'][$key]['order_item_id'] = $order_item_id;
                    $postOrderDetails['items'][$key]['qty_requested'] = $qty_requested;
                    $postOrderDetails['items'][$key]['resolution'] = $resolution;
                    $postOrderDetails['items'][$key]['condition'] = $condition;
                    $postOrderDetails['items'][$key]['reason'] = $reason;
                    if($reason == 'other') {
                        $postOrderDetails['items'][$key]['reason_other'] = $reason_other;
                    }
                }

                $postOrderDetails['rma_comment'] = $rma_comment;
                $postOrderDetails['form_key'] = $form_key;
                if ($shippingPreference) {
                    $postOrderDetails['shipping_preference'] = $shippingPreference;
                }
                $this->request->setPostValue($postOrderDetails);
            }
        }
    }

}

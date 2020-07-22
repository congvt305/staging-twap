<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/22/20
 * Time: 11:35 AM
 */

namespace Eguana\CustomRMA\Plugin;


use Magento\Framework\Exception\AlreadyExistsException;

class GuestSubmitPlugin
{
    /**
     * @var \Magento\Rma\Model\ResourceModel\Rma
     */
    private $rmaResource;
    /**
     * @var \Magento\Rma\Model\RmaFactory
     */
    private $rmaFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Rma\Model\ResourceModel\Rma $rmaResource,
        \Magento\Rma\Model\RmaFactory $rmaFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->rmaResource = $rmaResource;
        $this->rmaFactory = $rmaFactory;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Rma\Controller\Guest\Submit $subject
     * @param $result
     */
    public function afterExecute(\Magento\Rma\Controller\Guest\Submit $subject, $result)
    {
        $shippingPreference = $subject->getRequest()->getParam('shipping_preference');
        if ($shippingPreference) {
            $orderId = (int)$subject->getRequest()->getParam('order_id');
            try {
                $rmaModel = $this->rmaFactory->create();
                $this->rmaResource->load($rmaModel, $orderId, 'order_id');
                $rmaModel->setData('shipping_preference', $shippingPreference);
                $this->rmaResource->save($rmaModel);
            } catch (AlreadyExistsException $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return $result;
    }

}

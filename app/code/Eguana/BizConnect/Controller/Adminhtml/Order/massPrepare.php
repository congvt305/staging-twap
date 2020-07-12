<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/18/20, 9:10 AM
 *
 */

namespace Eguana\BizConnect\Controller\Adminhtml\Order;

use Magento\Sales\Model\OrderRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class massPrepare extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    public function __construct(
        CollectionFactory $collectionFactory,
        OrderRepository $orderRepository,
        Context $context,
        Filter $filter
    ) {
        parent::__construct($context, $filter);
        $this->orderRepository = $orderRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Eguana_BizConnect::prepare_order';
    protected function massAction(AbstractCollection $collection)
    {
        $countPreparingOrder = 0;
        foreach ($collection->getItems() as $order) {
            if (!$this->canPrepare($order)) {
                continue;
            }
            $this->prepareOrder($order->getEntityId());
            $countPreparingOrder++;
        }
        $countNonPreparedOrder = $collection->count() - $countPreparingOrder;
        if ($countNonPreparedOrder && $countPreparingOrder) {
            $this->messageManager->addErrorMessage(__('%1 order(s) were not put on preparing.', $countNonPreparedOrder));
        } elseif ($countNonPreparedOrder) {
            $this->messageManager->addErrorMessage(__('No order(s) were put on preparing.'));
        }

        if ($countPreparingOrder) {
            $this->messageManager->addSuccessMessage(__('You have put %1 order(s) on preparing.', $countPreparingOrder));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    private function prepareOrder($orderId)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderRepository->get($orderId);
        $order->setStatus('preparing');
        return (bool)$this->orderRepository->save($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool
     */
    private function canPrepare($order)
    {
        $notPreparableStates = [
            \Magento\Sales\Model\Order::STATE_CANCELED,
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
            \Magento\Sales\Model\Order::STATE_COMPLETE,
            \Magento\Sales\Model\Order::STATE_CLOSED,
            \Magento\Sales\Model\Order::STATE_HOLDED
        ];
        if (in_array($order->getState(), $notPreparableStates)) {
            return false;
        }
        return true;
    }
}

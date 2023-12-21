<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Observer\GraphQl\Quote\Submit;

use Amasty\CheckoutCore\Api\AdditionalFieldsManagementInterface;
use Amasty\CheckoutCore\Model\Account;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreateCustomerAccount implements ObserverInterface
{
    /**
     * @var Account
     */
    private $accountProcessor;

    /**
     * @var AdditionalFieldsManagementInterface
     */
    private $fieldsManagement;

    public function __construct(Account $accountProcessor, AdditionalFieldsManagementInterface $fieldsManagement)
    {
        $this->accountProcessor = $accountProcessor;
        $this->fieldsManagement = $fieldsManagement;
    }

    /**
     * Event `checkout_submit_all_after`
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getDataByKey('order');
        $fields = $this->fieldsManagement->getByQuoteId((int)$order->getQuoteId());
        if ($fields && $fields->getRegister()) {
            $this->accountProcessor->create((int)$order->getId(), $fields);
        }
    }
}

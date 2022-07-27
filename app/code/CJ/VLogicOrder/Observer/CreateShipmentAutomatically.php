<?php declare(strict_types=1);

namespace CJ\VLogicOrder\Observer;

use CJ\VLogicOrder\Model\Request\CreateOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class CreateShipmentAutomatically implements ObserverInterface
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CreateOrder
     */
    private $createOrder;

    /**
     * @param CreateOrder $createOrder
     * @param StoreRepositoryInterface $storeRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CreateOrder $createOrder,
        StoreRepositoryInterface $storeRepository,
        LoggerInterface $logger
    ){
        $this->createOrder = $createOrder;
        $this->storeRepository = $storeRepository;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');
        $order = $invoice->getOrder();
        if ($order->getStoreId() == $this->getMYSWSStoreId()) {
            $this->createOrder->createShipment($invoice->getOrder(), true);
        }
    }

    /**
     * @return int|null
     */
    public function getMYSWSStoreId()
    {
        try {
            $store = $this->storeRepository->get(self::MY_SWS_STORE_CODE);
            return $store->getId();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return null;
    }
}

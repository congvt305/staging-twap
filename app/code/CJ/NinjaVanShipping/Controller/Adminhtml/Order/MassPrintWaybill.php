<?php

declare(strict_types=1);

namespace CJ\NinjaVanShipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use CJ\NinjaVanShipping\Api\GenerateWaybillInterface;

class MassPrintWaybill extends Action implements HttpPostActionInterface
{
    const DEFAULT_STORE_ID = 1;

    const NINJAVAN_PREFIX = 'NV';

    const FILE_NAME = 'waybill_%s.pdf';

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var OrderCollectionFactory
     */
    protected OrderCollectionFactory $orderCollectionFactory;

    /**
     * @var GenerateWaybillInterface
     */
    protected GenerateWaybillInterface $generateWaybillService;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;

    /**
     * @var string
     */
    protected string $redirectUrl = 'sales/*/';

    /**
     * @param Action\Context $context
     * @param Filter $filter
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param GenerateWaybillInterface $generateWaybillService
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        OrderCollectionFactory $orderCollectionFactory,
        GenerateWaybillInterface $generateWaybillService,
        Filesystem $filesystem,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->generateWaybillService = $generateWaybillService;
        $this->filesystem = $filesystem;
        $this->fileFactory = $fileFactory;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $trackingNumbers = $orderNumbers = [];
        $storeId = null;

        $collection = $this->filter->getCollection($this->orderCollectionFactory->create());
        foreach ($collection->getItems() as $order) {
            $storeId = $order->getStoreId();
            $ninjavanTrackingNumber = $this->getNVTrackingNumber($order);
            if (empty($ninjavanTrackingNumber)) {
                continue;
            }
            $trackingNumbers[] = $ninjavanTrackingNumber;
            $orderNumbers[] = $order->getIncrementId();
        }

        try {
            if (empty($trackingNumbers)) {
                throw new LocalizedException(__('Not found any NinjaVan tracking numbers from selected orders. Please select other orders.'));
            }

            $responseContent = $this->generateWaybillService->process($trackingNumbers, $storeId ?? self::DEFAULT_STORE_ID);
            if ($responseContent) {
                $tempDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
                $filePath = self::FILE_NAME . uniqid() . '.pdf';
                $tempDir->writeFile($filePath, $responseContent);

                return $this->fileFactory->create(
                    sprintf(self::FILE_NAME, implode('-', $orderNumbers)),
                    [
                        'type' => 'filename',
                        'value' => $filePath,
                        'rm' => true
                    ],
                    DirectoryList::TMP,
                    'application/pdf'
                );
            }

            $this->messageManager->addErrorMessage(__('Printed Waybill failed. No response found'));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->redirectUrl);
        return $resultRedirect;
    }

    /**
     * @param $order
     * @return string
     */
    protected function getNVTrackingNumber($order): string
    {
        foreach ($order->getShipmentsCollection() as $shipment) {
            foreach ($shipment->getTracksCollection() as $track) {
                $trackNumberPrefix = substr($track->getTrackNumber(), 0, 2);
                if ($trackNumberPrefix != self::NINJAVAN_PREFIX) {
                    continue;
                }
                return $track->getTrackNumber();
            }
        }
        return '';
    }
}

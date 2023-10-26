<?php
namespace Magenest\Popup\Controller\Adminhtml\Log;

use Magenest\Popup\Model\ResourceModel\Log;
use Magenest\Popup\Model\ResourceModel\Log\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends Action
{
    /** @var Filter */
    protected $filter;

    /** @var CollectionFactory */
    protected $popupLogCollectionFactory;

    /** @var LoggerInterface */
    protected $logger;

    /** @var Log */
    private $popupLogResources;

    /**
     * MassDelete constructor.
     * @param CollectionFactory $popupLogCollectionFactory
     * @param Log $popupLogResources
     * @param Filter $filter
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        CollectionFactory $popupLogCollectionFactory,
        Log $popupLogResources,
        Filter $filter,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->popupLogCollectionFactory = $popupLogCollectionFactory;
        $this->popupLogResources = $popupLogResources;
        $this->filter = $filter;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->popupLogCollectionFactory->create());
            $count = 0;
            foreach ($collection as $item) {
                $this->popupLogResources->delete($item);
                $count++;
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Get ACL
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Popup::log');
    }
}

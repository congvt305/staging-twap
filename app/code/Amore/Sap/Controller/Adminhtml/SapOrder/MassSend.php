<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-22
 * Time: 오후 3:48
 */

namespace Amore\Sap\Controller\Adminhtml\SapOrder;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Ui\Component\MassAction\Filter;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassSend extends Action
{
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var SapOrderConfirmData
     */
    private $sapOrderConfirmData;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * MassSend constructor.
     * @param Action\Context $context
     * @param Json $json
     * @param Filter $filter
     * @param SapOrderConfirmData $sapOrderConfirmData
     * @param Request $request
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Json $json,
        Filter $filter,
        SapOrderConfirmData $sapOrderConfirmData,
        Request $request,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->filter = $filter;
        $this->sapOrderConfirmData = $sapOrderConfirmData;
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $countSendOrder = 0;
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $collection = $this->filter->getCollection($this->collectionFactory->create());

//        foreach ($collection->getItems() as $order) {
//
//        }


        return $resultRedirect->setPath('*/*/index');
    }
}

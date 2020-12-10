<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/11/20
 * Time: 12:11 AM
 */

namespace Eguana\Redemption\Controller\Adminhtml\Redemption;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Eguana\Redemption\Model\Source\AvailableStores;

/**
 * This class is used to to show counter list according to the store view
 * Class AjaxCall
 */
class AjaxCall extends Action
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var AvailableStores
     */
    private $availableStores;

    /**
     * AjaxCall constructor.
     *
     * @param ResultFactory $resultFactory
     * @param Context $context
     * @param AvailableStores $availableStores
     */
    public function __construct(
        ResultFactory $resultFactory,
        Context $context,
        AvailableStores $availableStores
    ) {
        $this->resultFactory = $resultFactory;
        $this->context = $context;
        $this->availableStores = $availableStores;
        parent::__construct($context);
    }
    /**
     * This method is used to get the counter store list from store locator according
     * to the store view selection
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($this->_request->isAjax()) {
            $storeId = $this->context->getRequest()->getParam('store_id');
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(
                [
                    "message" => "Store list according to store view",
                    "suceess" => true,
                    "storelist" => $this->availableStores->getStoreListByStoreId($storeId)
                ]
            );
            return $resultJson;
        } elseif (!$this->_request->isAjax()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
    }
}

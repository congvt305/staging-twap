<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 3/11/20
 * Time: 6:10 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Reservation;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Used to get available store locators against store id
 *
 * Class AjaxAvailableStores
 */
class AjaxAvailableStores extends Action
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        JsonFactory $resultJsonFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Method used save store id
     *
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $storeId = $this->getRequest()->getParam('store_id');
        if ($this->_request->isAjax() && $storeId) {
            $this->dataPersistor->set('selected_store_id', $storeId);
            $result->setData(['success' => true]);
        } else {
            $result->setData(['success' => false]);
        }
        return $result;
    }
}

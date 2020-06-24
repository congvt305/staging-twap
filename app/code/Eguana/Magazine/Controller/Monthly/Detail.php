<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/23/20
 * Time: 1:15 PM
 */
namespace Eguana\Magazine\Controller\Monthly;

use Eguana\Magazine\Model\ResourceModel\Magazine\CollectionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Eguana\Magazine\Model\ResourceModel\Magazine\Collection;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class Magazine
 */
class Detail extends Action
{

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LoggerInterface;
     */
    private $logger;
    /**
     * Construct
     *
     * @param Context $context
     * @param View  $magazine
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManagerInterface,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\App\ResponseInterface|ResultInterfaceAlias|void
     */
    public function execute()
    {
        $month = $this->getRequest()->getParam('month');
        $year = $this->getRequest()->getParam('year');
        if (isset($month) && isset($year)) {
            $magazineCollection = $this->getCollection($month, $year);
            if (empty($magazineCollection->getData())) {
                $resultRedirect = $this->resultPageFactory->create();
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
        } elseif (!isset($month) || !isset($year)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }

    /**
     * check if magazine exist with in dates
     * @param $month
     * @param $year
     * @return Collection
     */
    private function getCollection($month, $year)
    {
        try {
            $startDate = $this->dateTime->gmtDate(
                'Y-m-d H:i:s',
                $year . '-' . $month . '-' . 1 . ' 00:00:00'
            );
            $endDate = $this->dateTime->gmtDate(
                'Y-m-d H:i:s',
                $year . '-' . $month . '-' . 31 . ' 00:00:00'
            );
            $magazineCollection = $this->collectionFactory->create();
            $storeId =  $this->storeManagerInterface->getStore()->getId();
            $magazineCollection = $this->collectionFactory->create();
            $magazineCollection->addFieldToFilter(
                ['store_id','store_id','store_id','store_id'],
                [["like" => '%' . $storeId . ',%'],
                    ["like" => '%,' . $storeId . ',%'],
                    ["like" => '%,' . $storeId . '%'],
                    ["in" => ['0', $storeId]]]
            )->setOrder(
                "sort_order",
                'ASC'
            )->addFieldToFilter(
                'show_date',
                ['gteq' => $startDate]
            )->addFieldToFilter(
                'show_date',
                ['lteq' => $endDate]
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $magazineCollection;
    }
}

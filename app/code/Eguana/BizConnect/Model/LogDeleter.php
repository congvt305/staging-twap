<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/09/07
 * Time: 1:37 PM
 */

namespace Eguana\BizConnect\Model;

use Magento\Store\Model\StoreManagerInterface;

class LogDeleter
{
    /**
     * @var ResourceModel\LoggedOperation\CollectionFactory
     */
    private $loggedOperationCollectionFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var Source\Config
     */
    private $helper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * LogDeleter constructor.
     * @param ResourceModel\LoggedOperation\CollectionFactory $loggedOperationCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Psr\Log\LoggerInterface $logger
     * @param Source\Config $helper
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        \Eguana\BizConnect\Model\ResourceModel\LoggedOperation\CollectionFactory $loggedOperationCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Psr\Log\LoggerInterface $logger,
        \Eguana\BizConnect\Model\Source\Config $helper,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->loggedOperationCollectionFactory = $loggedOperationCollectionFactory;
        $this->date = $date;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    public function logDeleter()
    {
        $date = $this->date->gmtDate();

        if ($this->helper->getActive()) {
            /** @var \Eguana\BizConnect\Model\ResourceModel\LoggedOperation\Collection $collection */
            $collection = $this->loggedOperationCollectionFactory->create();
            $collection->addFieldToFilter('start_time', array('lteq' => $date));
            $collection->getSelect()->limit($this->getSelectLimit());

            /** @var \Eguana\BizConnect\Model\LoggedOperation $loggedOperation */
            foreach ($collection as $loggedOperation) {
                $loggedOperation->delete();
            }
        }
    }

    protected function getSelectLimit()
    {
        return $this->helper->getNumbersToDelete() ? $this->helper->getNumbersToDelete() : 100;
    }
}

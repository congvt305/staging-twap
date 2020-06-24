<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/18/20
 * Time: 3:10 AM
 */
namespace Eguana\Magazine\Controller\Index;

use Eguana\Magazine\Model\ResourceModel\Magazine\CollectionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface;
use Eguana\Magazine\ViewModel\Index\MagazineList;
use Magento\Framework\Controller\ResultFactory;

/**
 * Action for index index
 *
 * Class Index
 */
class Index extends Action
{

    /** @var Page */
    private $resultPageFactory;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    /**
     * @var MagazineList
     */
    private $magazineList;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ManagerInterface $managerInterface
     * @param CollectionFactory $collectionFactory
     * @param MagazineList $magazineList
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ManagerInterface $managerInterface,
        CollectionFactory $collectionFactory,
        MagazineList $magazineList
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->managerInterface = $managerInterface;
        $this->collectionFactory = $collectionFactory;
        $this->magazineList = $magazineList;
    }

    /**
     *
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|void
     */
    public function execute()
    {
        $month = $this->getRequest()->getParam('month');
        $year = $this->getRequest()->getParam('year');
        $params = $this->magazineList->getParams();
        if ($params) {
            $collection = $this->getcollectionByMonth($params['start'], $params['end']);
            if (empty($collection->getData())) {
                $this->managerInterface->addErrorMessage('No magazine exist with in this month');
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('/magazine');
                return $resultRedirect;
            }
        } elseif (isset($month) && !isset($year) || isset($year) && !isset($month)) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/magazine');
            return $resultRedirect;
        }

        return $this->resultPageFactory->create();
    }

    /**
     * this check if magazine exist with param month
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getcollectionByMonth($startDate, $endDate)
    {
        $collection = $this->collectionFactory->create();
        $collection = $this->magazineList->getCollectionByStoreFilter($collection);
        $collection = $this->magazineList->getDateFilterCollection($collection, $startDate, $endDate);
        return $collection;
    }
}

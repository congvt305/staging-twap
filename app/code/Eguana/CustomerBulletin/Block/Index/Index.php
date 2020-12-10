<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Block\Index;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\Faq\Api\FaqRepositoryInterface;
use Eguana\Faq\Model\ResourceModel\Faq\CollectionFactory as FaqCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket\Collection;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Pricing\Helper\Data as priceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Pager;
use Psr\Log\LoggerInterface;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class Index
 */
class Index extends Template
{
    /**
     * @var FaqCollectionFactory
     */
    private $faqCollectionFactory;

    /**
     * @var FaqRepositoryInterface
     */
    private $faqRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var CollectionFactory
     */
    private $ticketCollectionFactory;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @var priceHelper
     */
    private $priceHepler;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * Index constructor.
     * @param Context $context
     * @param RequestInterface $request
     * @param FaqRepositoryInterface $faqRepository
     * @param FaqCollectionFactory $faqCollectionFactory
     * @param priceHelper $priceHepler
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Session $customerSession
     * @param LoggerInterface $logger
     * @param TicketRepositoryInterface $ticketRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        Data $helperData,
        FaqRepositoryInterface $faqRepository,
        FaqCollectionFactory $faqCollectionFactory,
        priceHelper $priceHepler,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Session $customerSession,
        LoggerInterface $logger,
        TicketRepositoryInterface $ticketRepository,
        CollectionFactory  $collectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->faqCollectionFactory = $faqCollectionFactory;
        $this->faqRepository = $faqRepository;
        $this->helperData = $helperData;
        $this->requestInterface = $request;
        $this->ticketRepository = $ticketRepository;
        $this->ticketCollectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->priceHepler = $priceHepler;
        parent::__construct($context);
    }

    /**
     * prepare layout
     *
     * @return $this|Index
     */
    protected function _prepareLayout()
    {
        try {
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link' => $this->storeManager->getStore()->getBaseUrl()
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'account',
                    [
                        'label' => __('My Account'),
                        'title' => __('My Account'),
                        'link' => $this->storeManager->getStore()->getBaseUrl() . 'customer/account/'
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'tickets',
                    [
                        'label' => __('My Tickets'),
                        'title' => __('My Tickets'),
                    ]
                );
            }
            parent::_prepareLayout();
            $this->pageConfig->getTitle()->set(__('My Tickets'));
            if ($this->getTicketCollection()) {
                $pager = $this->getLayout()->createBlock(
                    Pager::class,
                    'custom.history.pager'
                )->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
                    ->setShowPerPage(true)->setCollection(
                        $this->getTicketCollection()
                    );
                $this->setChild('pager', $pager);
                $this->getTicketCollection()->load();
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        parent::_prepareLayout();
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml() : string
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get collection of ticket using collection factory
     *
     * @return Collection
     */
    public function getTicketCollection()
    {
        $sortOrder = $this->helperData->getGeneralConfig('configuration/sort_order');
        $customerId = $this->customerSession->getCustomer()->getId();
        $page       = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize   = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 5;
        $collection = $this->ticketCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $collection->setOrder('ticket_id', $sortOrder);
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }
    /**
     * Get form action URL for POST ticket request
     *
     * @return string
     */
    public function getFormUrl() : string
    {
        return $this->_urlBuilder->getUrl('ticket/index/createticket');
    }

    /**
     * Get ticket detail page URL
     *
     * @param $ticketId
     * @return string
     */
    public function getTicketUrl($ticketId) : string
    {
        return $this->_urlBuilder->getUrl('ticket/index/detail/ticket_id/' . $ticketId, ['_secure' => true]);
    }

    /**
     * get label of status by using its value
     *
     * @param $status
     * @return string
     */
    public function getStatus($status) : string
    {
        if ($status == 1) {
            return "Open";
        } elseif ($status == 0) {
            return "Close";
        } else {
            return "Hold";
        }
    }

    /**
     * get label of Note status by using its value
     *
     * @param $status
     * @return string
     */
    public function getNoteStatus($status) : string
    {
        if ($status == 1) {
            return "Read";
        } else {
            return "Unread";
        }
    }

    /**
     * get faq list question from its repository
     *
     * @return array
     */
    public function faqList()
    {
        $faqList = [];

        try {
            $currentStoreId = $this->storeManager->getStore()->getId();
            $search = $this->searchCriteriaBuilder->addFilter('is_active', 1)
                ->addFilter('store_id', $currentStoreId)->create();
            $faqList = $this->faqRepository->getList($search)->getItems();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        $title = [];
        foreach ($faqList as $faq) {
            $title[] = $faq->getTitle();
        }
        return $title;
    }

    /**
     * Get FAQ question search url
     *
     * @param $name
     * @return string
     */
    public function getFaqQuestionUrl($name) : string
    {
        $url =  $this->_urlBuilder->getUrl('faq/index/search?faqSearchVal=' . $name);
        $url = substr($url, 0, -1);
        return $url;
    }
}

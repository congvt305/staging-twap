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

namespace Sapt\EguanaCustomerBulletin\Block\Index;

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
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Pager;
use Psr\Log\LoggerInterface;
use Eguana\CustomerBulletin\Block\Index\Index as EguanaIndex;
use Magento\Framework\View\Element\Template;
/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class Index
 */
class Index extends EguanaIndex
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
        parent::__construct(
            $context,
            $request,
            $helperData,
            $faqRepository,
            $faqCollectionFactory,
            $priceHepler,
            $storeManager,
            $searchCriteriaBuilder,
            $customerSession,
            $logger,
            $ticketRepository,
            $collectionFactory
        );
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
                        'title' => __('Home'),
                        'link' => $this->storeManager->getStore()->getBaseUrl()
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'account',
                    [
                        'label' => __('My Page'),
                        'title' => __('My Page'),
                        'link' => $this->storeManager->getStore()->getBaseUrl() . 'customer/account/'
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'tickets',
                    [
                        'label' => __('1:1 Inquiry'),
                        'title' => __('1:1 Inquiry'),
                    ]
                );
            }
            Template::_prepareLayout();
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
        Template::_prepareLayout();
        return $this;
    }
 }

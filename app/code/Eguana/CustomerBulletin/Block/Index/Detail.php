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

use Eguana\CustomerBulletin\Helper\Data;
use Eguana\CustomerBulletin\Model\ResourceModel\Note\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Eguana\CustomerBulletin\Model\ResourceModel\Note\Collection;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\File\Size;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is used for the detail ticket templete
 *
 * Class Detail
 */
class Detail extends Template
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CollectionFactory
     */
    private $noteCollectionFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var Size
     */
    private $fileSize;

    /**
     * Detail constructor.
     * @param Context $context
     * @param RequestInterface $request
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $noteCollectionFactory
     * @param LoggerInterface $logger
     * @param Size $fileSize
     * @param FormKey $formKey
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        Data $helperData,
        StoreManagerInterface $storeManager,
        CollectionFactory $noteCollectionFactory,
        LoggerInterface $logger,
        Size $fileSize,
        FormKey $formKey
    ) {
        $this->formKey = $formKey;
        $this->request = $request;
        $this->fileSize = $fileSize;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->noteCollectionFactory = $noteCollectionFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }
    /**
     * To set page title and breadcrumb
     * This function will set the page title according to the current ticket subject
     * and it will also set the breadcrumb
     *
     * @return $this|Detail
     */
    public function prepareLayout()
    {
        parent::_prepareLayout();
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
                    'support_ticket',
                    [
                        'label' => __('Support Ticket'),
                        'title' => __('Support Ticket'),
                        'link' => $this->_urlBuilder->getUrl('ticket/')
                    ]
                );
                if (!empty($this->getTicketCollection())) {
                    $breadcrumbsBlock->addCrumb(
                        'ticket_subject',
                        [
                            'label' => __($this->getSubject()),
                            'title' => __($this->getSubject())
                        ]
                    );
                }
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this;
    }

    /**
     * Get Ticket close URL of controller
     *
     * @param $ticketId
     * @return string
     */
    public function getTicketCloseAction($ticketId) : string
    {
        return $this->_urlBuilder->getUrl('ticket/index/close/ticket_id/' . $ticketId, ['_secure' => true]);
    }

    /**
     * Get form action URL for POST ticket request
     *
     * @return string
     */
    public function getFormAction() : string
    {
        return $this->_urlBuilder->getUrl('ticket/index/saveMsg');
    }

    /**
     * Get Ticket save URL of controller
     *
     * @return string
     */
    public function getNoteSaveUrl()
    {
        return $this->_urlBuilder->getUrl('ticket/note/save/', ['_secure' => true]);
    }

    /**
     * Get form key for form for sending it with post request
     *
     * @return string
     */
    public function getFormKey() : string
    {
        $formKey = '';
        try {
            return $this->formKey->getFormKey();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $formKey;
    }
    /**
     * get collection of note
     *
     * @return Collection
     */
    public function getNoteCollection()
    {
        $notecollection = $this->noteCollectionFactory->create();
        $notecollection->addFieldToFilter('ticket_id', ['eq' => $this->request->getParam('ticket_id')])
            ->setOrder('creation_time', 'ASC');
        return $notecollection;
    }

    /**
     * Get maximum allowed file size in bytes.
     *
     * @return float
     */
    public function getMaxFileSize()
    {
        return $this->fileSize->convertSizeToInteger($this->getMaxFileSizeMb() . 'M');
    }

    /**
     * Get maximum allowed file size in Mb.
     *
     * @return float
     */
    public function getMaxFileSizeMb()
    {
        return $this->helperData->getGeneralConfig('configuration/file_size');
    }

    /**
     * Get allowed file extensions.
     *
     * @return string
     */
    public function getAllowedExtensions()
    {
        return $this->helperData->getGeneralConfig('configuration/file_types');
    }
}

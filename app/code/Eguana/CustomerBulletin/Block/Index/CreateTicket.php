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
use Magento\Framework\File\Size;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class ii used for close ticket templete
 *
 * Class CreateTicket
 */
class CreateTicket extends Template
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Size
     */
    private $fileSize;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * CreateTicket constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Size $fileSize
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Size $fileSize,
        Data $helperData,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
        $this->fileSize = $fileSize;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * To set page title and breadcrumb
     * This function will set the page title New Ticket
     * and it will also set the breadcrumb
     *
     * @return $this|Detail
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('New Ticket'));
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
                $breadcrumbsBlock->addCrumb(
                    'main_title',
                    [
                            'label' => __('New Ticket'),
                            'title' => __('Add new ticket')
                    ]
                );
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this;
    }

    /**
     * Get form action URL for POST ticket request
     *
     * @return string
     */
    public function getFormAction() : string
    {
        return $this->_urlBuilder->getUrl('ticket/index/createticket');
    }

    /**
     * Get FAQ question search url
     *
     * @param $name
     * @return string
     */
    public function getFaqQuestionUrl($name) : string
    {
        return $this->_urlBuilder->getUrl('faq/index/search?faqSearchVal=' . $name);
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

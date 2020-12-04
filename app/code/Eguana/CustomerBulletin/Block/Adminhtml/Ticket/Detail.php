<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Block\Adminhtml\Ticket;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\File\Size;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Form\FormKey;
use Eguana\CustomerBulletin\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class Detail for sending the messages and check the detail of ticket
 */
class Detail extends Template
{
    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param Size $fileSize
     * @param LoggerInterface $logger
     * @param FormKey $formKey
     */
    public function __construct(
        Context $context,
        Data $helperData,
        StoreManagerInterface $storeManager,
        Size $fileSize,
        LoggerInterface $logger,
        FormKey $formKey
    ) {
        $this->formKey = $formKey;
        $this->helperData = $helperData;
        $this->fileSize = $fileSize;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get form action URL for POST Note request
     *
     * @return string
     */
    public function getFormAction() : string
    {
        return $this->_urlBuilder->getUrl('ticket/ticket/detail');
    }

    /**
     * Get Ticket close URL of controller
     *
     * @param $ticketId
     * @return string
     */
    public function getTicketCloseAction($ticketId) : string
    {
        return $this->_urlBuilder->getUrl(
            'ticket/ticket/ticketclose/ticket_id/' . $ticketId,
            ['_secure' => true]
        );
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

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
use Eguana\CustomerBulletin\Block\Index\Detail as EguanaDetail;

/**
 * This class is used for the detail ticket templete
 *
 * Class Detail
 */
class Detail extends EguanaDetail
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
        parent::__construct(
            $context,
            $request,
            $helperData,
            $storeManager,
            $noteCollectionFactory,
            $logger,
            $fileSize,
            $formKey
        );
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
        Template::_prepareLayout();
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
                    'support_ticket',
                    [
                        'label' => __('1:1 Inquiry'),
                        'title' => __('1:1 Inquiry'),
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


}

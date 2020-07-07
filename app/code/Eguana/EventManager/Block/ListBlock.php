<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 30/6/20
 * Time: 12:38 PM
 */
namespace Eguana\EventManager\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * This class used to add breadcrumbs and title
 *
 * Class ListBlock
 */
class ListBlock extends Template
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
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * Index constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $requestInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        RequestInterface $requestInterface,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->requestInterface = $requestInterface;
        parent::__construct($context, $data);
    }

    /**
     * To set page title and breadcrumb
     * This function will set the page title according to the current Event title
     * and it will also set the breadcrumb
     * @return $this|ListBlock
     */
    public function _prepareLayout()
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
                    'event',
                    [
                        'label' => __('Events'),
                        'title' => __('Events'),
                    ]
                );
                $this->pageConfig->getTitle()->set(__('Events'));
                $fullActionName = $this->requestInterface->getFullActionName();
                if ($fullActionName == "events_index_index") {
                    $breadcrumbsBlock->addCrumb(
                        'main_title',
                        [
                            'label' => __('Current Events'),
                            'title' => __('Current Events')
                        ]
                    );
                } elseif ($fullActionName == "events_previous_index") {
                    $breadcrumbsBlock->addCrumb(
                        'main_title',
                        [
                            'label' => __('Previous Events'),
                            'title' => __('Previous Events')
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

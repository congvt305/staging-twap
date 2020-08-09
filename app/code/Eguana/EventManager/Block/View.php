<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 1/7/20
 * Time: 11:42 PM
 */
namespace Eguana\EventManager\Block;

use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Eguana\EventManager\Model\EventManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * class View
 *
 * block for details.phtml
 */
class View extends Template implements IdentityInterface
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
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepository;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * EventManager constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param EventManagerRepositoryInterface $eventManagerRepository
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        EventManagerRepositoryInterface $eventManagerRepository,
        StoreManagerInterface $storeManager,
        RequestInterface $requestInterface,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->eventManagerRepository = $eventManagerRepository;
        $this->requestInterface = $requestInterface;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [EventManager::CACHE_TAG];
    }

    /**
     * get event method
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        /** @var EventManager $eventManager */
        $id = $this->requestInterface->getParam('id');
        $eventManager = $this->eventManagerRepository->getById($id);
        return $eventManager;
    }

    /**
     * To set page title and breadcrumb
     * This function will set the page title according to the current event title
     * and it will also set the breadcrumb
     * @return $this|View
     */

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        try {
            $this->pageConfig->getTitle()->set($this->getEventManager()->getTitle());
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
                        'link' => $this->storeManager->getStore()->getBaseUrl() . 'events'
                    ]
                );
                if (!empty($this->getEventManager()->getData())) {
                    $breadcrumbsBlock->addCrumb(
                        'main_title',
                        [
                            'label' => __($this->getEventManager()->getTitle()),
                            'title' => __($this->getEventManager()->getTitle())
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

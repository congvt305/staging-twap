<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 22/10/20
 * Time: 10:00 PM
 */
namespace Eguana\EventReservation\Block\Event;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Model\Event;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Block class for event reservation
 *
 * Class Reservation
 */
class Reservation extends Template implements IdentityInterface
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
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param RequestInterface $requestInterface
     * @param StoreManagerInterface $storeManager
     * @param EventRepositoryInterface $eventRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        RequestInterface $requestInterface,
        StoreManagerInterface $storeManager,
        EventRepositoryInterface $eventRepository,
        array $data = []
    ) {
        $this->logger           = $logger;
        $this->storeManager     = $storeManager;
        $this->eventRepository  = $eventRepository;
        $this->requestInterface = $requestInterface;
        parent::__construct($context, $data);
    }

    /**
     * Get identities
     *
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [Event::CACHE_TAG];
    }

    /**
     * Get event
     *
     * @return EventInterface|Event
     */
    public function getEvent()
    {
        /** @var Event $event */
        $id = $this->requestInterface->getParam('id');
        try {
            $event = $this->eventRepository->getById($id);
        } catch (\Exception $e) {
            $this->logger->error('Error while fetching Event:' . $e->getMessage());
        }
        return isset($event) ? $event : '';
    }

    /**
     * To set page title and breadcrumb
     *
     * @return $this|Reservation
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        try {
            $event = $this->getEvent();
            if (!empty($event)) {
                $metaTitle = $event->getMetaTitle();
                $title = $metaTitle ? $metaTitle : $event->getTitle();
                $this->pageConfig->getTitle()->set($title);
                $this->pageConfig->setMetaTitle($metaTitle);
                $this->pageConfig->setKeywords($event->getMetaKeywords());
                $this->pageConfig->setDescription($event->getMetaDescription());
            }
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
                if (!empty($event)) {
                    $breadcrumbsBlock->addCrumb(
                        'main_title',
                        [
                            'label' => __($event->getTitle()),
                            'title' => __($event->getTitle())
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

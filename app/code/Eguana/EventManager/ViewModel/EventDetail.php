<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 1/7/20
 * Time: 11:50 AM
 */
namespace Eguana\EventManager\ViewModel;

use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

/**
 * This ViewModel is used to show single Event detail
 *
 * Class EventDetail
 */
class EventDetail implements ArgumentInterface
{
    /**
     * Constant
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepository;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * EventManager constructor.
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     * @param EventManagerRepositoryInterface $eventManagerRepository
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Http $request,
        FilterProvider $filterProvider,
        EventManagerRepositoryInterface $eventManagerRepository,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->request = $request;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
        $this->eventManagerRepository = $eventManagerRepository;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * Get Event id
     *
     * @return mixed
     */
    private function getEventManagerId()
    {
        return $this->request->getParam('id');
    }

    /**
     * get Event method
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->eventManagerRepository->getById($this->getEventManagerId());
        return $eventManager;
    }

    /**
     * To filter the content
     * This function will get the content, specially the page builder content and make it renderable at frontend.
     * @param $content
     * @return mixed
     */
    public function contentFiltering($content)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($content);
    }

    /**
     * get store id
     * @return int
     */
    public function getStoreId()
    {
        try {
            return $this->_storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * This method is used to change the date format
     * @param $date
     * @return string
     */
    public function changeDateFormat($date)
    {
        try {
            return $this->timezone->date($date)->format(self::DATE_FORMAT);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }
}

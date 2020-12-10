<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: shahroz
 * Date: 5/11/20
 * Time: 6:09 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Model;

use Eguana\CustomerBulletin\Api\NoteRepositoryInterface;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreRepository;
use Psr\Log\LoggerInterface;

/**
 * This class is used to close ticket after specific duaration
 *
 * Class TicketCloser
 */
class TicketCloser
{

    const  ENABLE_SUPPORT = 'ticket_managment/configuration/enabled';
    const  CLOSED_TICKET = 'ticket_managment/configuration/ticket_close_duration';

    /**
     * @var NoteRepositoryInterface
     */
    private $noteRepositoryInterface;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepositoryInterface;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * ProcessTicket constructor.
     * @param NoteRepositoryInterface $noteRepositoryInterface
     * @param TicketRepositoryInterface $ticketRepositoryInterface
     * @param StoreRepository $storeRepository
     * @param Data $dataHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        NoteRepositoryInterface $noteRepositoryInterface,
        TicketRepositoryInterface $ticketRepositoryInterface,
        StoreRepository $storeRepository,
        Data $dataHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        $this->noteRepositoryInterface = $noteRepositoryInterface;
        $this->ticketRepositoryInterface = $ticketRepositoryInterface;
        $this->storeRepository = $storeRepository;
        $this->dataHelper = $dataHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    public function closeTicket() : bool
    {
        try {
            $stores = $this->storeRepository->getList();
            foreach ($stores as $store) {
                $storeId = $store->getId();
                $isActive = $this->dataHelper->getConfigValue(self::ENABLE_SUPPORT, $storeId);
                if ($isActive) {
                    $fromDate = $this->dateTime->date(
                        'Y-m-d H:i:s',
                        '-' . $this->getTicketClosedLimit($storeId) . ' days'
                    );
                    $this->processTicket($storeId, $fromDate);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return true;
    }
    /**
     * this function process tickets
     *
     * @param $storeId
     * @param $fromDate
     */
    private function processTicket($storeId, $fromDate) : void
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('store_id', $storeId)
                ->addFilter('status', 1)
                ->addFilter('creation_time', $fromDate, 'lteq')
                ->create();
            $tickets = $this->ticketRepositoryInterface->getList($searchCriteria)->getItems();
            foreach ($tickets as $ticket) {
                $this->closedTicket($ticket, $fromDate);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * this function filter tickets and closed according to configurqation
     *
     * @param $ticket
     * @param $fromDate
     */
    private function closedTicket($ticket, $fromDate):void
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('ticket_id', $ticket->getTicketId())
                ->addFilter('creation_time', $fromDate, 'gteq')
                ->create();
            $count = $this->noteRepositoryInterface->getList($searchCriteria)->getTotalCount();

            if ($count == 0) {
                $ticket->setStatus(0);
                $this->ticketRepositoryInterface->save($ticket);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * this function will return days limit to closed
     *
     * @param $storeId
     * @return string
     */
    private function getTicketClosedLimit($storeId):string
    {
        return $this->dataHelper->getConfigValue(self::CLOSED_TICKET, $storeId);
    }
}

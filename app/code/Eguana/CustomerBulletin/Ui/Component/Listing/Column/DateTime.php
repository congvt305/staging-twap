<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 8/12/20
 * Time: 9:30 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Ui\Component\Listing\Column;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeAlias;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Used to show date time according to timezone
 *
 * Class DateTime
 */
class DateTime extends Column
{
    /**
     * @var DateTimeAlias
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param DateTimeAlias $dateTime
     * @param TimezoneInterface $timezone
     * @param TicketRepositoryInterface $ticketRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        DateTimeAlias $dateTime,
        TimezoneInterface $timezone,
        TicketRepositoryInterface $ticketRepository,
        array $components = [],
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->ticketRepository = $ticketRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) : array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['ticket_id'])) {
                    $ticket = $this->ticketRepository->getById($item['ticket_id']);
                    $item['creation_time'] = $this->convertDateTime($item['creation_time'], $ticket->getStoreId());
                    $item['update_time'] = $this->convertDateTime($item['update_time'], $ticket->getStoreId());
                }
            }
        }
        return $dataSource;
    }

    /**
     * Convert date time
     *
     * @param $dateTime
     * @param $storeId
     * @return string
     */
    private function convertDateTime($dateTime, $storeId)
    {
        $defaultTimeZone = $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_STORE, $storeId);
        $formatedDate = $this->timezone->formatDateTime($dateTime, 2, 3, null, $defaultTimeZone);
        $pos = strrpos($formatedDate, ',');

        if ($pos !== false) {
            $formatedDate = substr_replace($formatedDate, ' ', $pos, strlen(', '));
        }
        return $formatedDate;
    }
}

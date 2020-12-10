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

namespace Eguana\CustomerBulletin\Block\Adminhtml\Ticket\Edit\Buttons;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Magento\Backend\Block\Widget\Context;
use Psr\Log\LoggerInterface;

/**
 * Class Generic which is extended by other block classes (Back, Delete, Reset, Save)
 */
class Generic
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * Generic constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TicketRepositoryInterface $ticketRepository
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TicketRepositoryInterface $ticketRepository
    ) {
        $this->logger            = $logger;
        $this->context           = $context;
        $this->ticketRepository  = $ticketRepository;
    }

    /**
     * Get ticket_id
     *
     * @return int|null
     */
    public function getId()
    {
        try {
            if (empty($this->context->getRequest()->getParam('ticket_id'))) {
                return null;
            }

            return $this->ticketRepository->getById(
                $this->context->getRequest()->getParam('ticket_id')
            )->getId();
        } catch (\Exception $e) {
            $this->logger->info('Generic Block Exception', $e->getMessage());
        }

        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = []) : string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 19/10/20
 * Time: 06:54 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Block\Adminhtml\Event\Edit;

use Eguana\EventReservation\Api\EventRepositoryInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * For all form buttons
 *
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @param Context $context
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        EventRepositoryInterface $eventRepository
    ) {
        $this->context          = $context;
        $this->eventRepository  = $eventRepository;
    }

    /**
     * Return event ID
     *
     * @return int|null
     */
    public function getEventId()
    {
        try {
            if (empty($this->context->getRequest()->getParam('event_id'))) {
                return null;
            }

            return $this->eventRepository->getById(
                $this->context->getRequest()->getParam('event_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
            $e->getMessage();
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = []) : string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

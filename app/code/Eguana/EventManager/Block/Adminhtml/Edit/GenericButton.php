<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 5:50 PM
 */
namespace Eguana\EventManager\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Generic class for all buttons
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
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepository;

    /**
     * @param Context $context
     * @param EventManagerRepositoryInterface $eventManagerRepository
     */
    public function __construct(
        Context $context,
        EventManagerRepositoryInterface $eventManagerRepository
    ) {
        $this->context = $context;
        $this->eventManagerRepository = $eventManagerRepository;
    }

    /**
     * Return CMS block ID
     *
     * @return int|null
     */
    public function getId()
    {
        try {
            if (empty($this->context->getRequest()->getParam('entity_id'))) {
                return null;
            }
            return $this->eventManagerRepository->getById(
                $this->context->getRequest()->getParam('entity_id')
            )->getEntityId();
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
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

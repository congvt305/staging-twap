<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 5:54 AM
 */

namespace Eguana\Magazine\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GenericButton
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var MagazineRepositoryInterface
     */
    private $magazineRepository;

    /**
     * @param Context $context
     * @param MagazineRepositoryInterface $magazineRepository
     */
    public function __construct(
        Context $context,
        MagazineRepositoryInterface $magazineRepository
    ) {
        $this->context = $context;
        $this->magazineRepository = $magazineRepository;
    }

    /**
     * Return CMS block ID
     *
     * @return int|null
     */
    public function getId()
    {
        try {
            return $this->magazineRepository->getById(
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

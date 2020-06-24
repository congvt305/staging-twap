<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Eguana\Faq\Api\FaqRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 *
 * Eguana\Faq\Block\Adminhtml\Edit
 */
class GenericButton
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var FaqRepositoryInterface
     */
    private $faqRepository;

    /**
     * @param Context $context
     * @param FaqRepositoryInterface $faqRepository
     */
    public function __construct(
        Context $context,
        FaqRepositoryInterface $faqRepository
    ) {
        $this->context = $context;
        $this->faqRepository = $faqRepository;
    }

    /**
     * Return CMS block ID
     *
     * @return int|null
     */
    public function getId()
    {
        try {
            return $this->faqRepository->getById(
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

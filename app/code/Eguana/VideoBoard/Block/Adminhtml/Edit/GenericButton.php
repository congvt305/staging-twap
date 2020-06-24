<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 11/6/20
 * Time: 6:03 PM
 */
namespace Eguana\VideoBoard\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
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
     * @var VideoBoardRepositoryInterface
     */
    private $videoBoardRepository;

    /**
     * @param Context $context
     * @param VideoBoardRepositoryInterface $videoBoardRepository
     */
    public function __construct(
        Context $context,
        VideoBoardRepositoryInterface $videoBoardRepository
    ) {
        $this->context = $context;
        $this->videoBoardRepository = $videoBoardRepository;
    }

    /**
     * Return CMS block ID
     *
     * @return int|null
     */
    public function getId()
    {
        try {
            if (empty($this->context->getRequest()->getParam('entity_id'))):
                    return null;
            endif;
            return $this->videoBoardRepository->getById(
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

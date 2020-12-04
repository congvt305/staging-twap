<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/10/20
 * Time: 1:10 PM
 */
namespace Eguana\Redemption\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Eguana\Redemption\Api\RedemptionRepositoryInterface;
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
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * GenericButton constructor.
     *
     * @param Context $context
     * @param RedemptionRepositoryInterface $redemptionRepository
     */
    public function __construct(
        Context $context,
        RedemptionRepositoryInterface $redemptionRepository
    ) {
        $this->context = $context;
        $this->redemptionRepository = $redemptionRepository;
    }

    /**
     * Return Redemption ID
     *
     * @return int|null
     */
    public function getId()
    {
        try {
            if (empty($this->context->getRequest()->getParam('redemption_id'))) {
                return null;
            }
            return $this->context->getRequest()->getParam('redemption_id');
        } catch (NoSuchEntityException $e) {
            $e->getMessage();
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

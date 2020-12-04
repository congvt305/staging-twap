<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 5:50 PM
 */
declare(strict_types=1);

namespace Eguana\NewsBoard\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var NewsRepositoryInterface
     */
    private $newsRepository;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param NewsRepositoryInterface $newsRepository
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        NewsRepositoryInterface $newsRepository
    ) {
        $this->context = $context;
        $this->logger  = $logger;
        $this->newsRepository = $newsRepository;
    }

    /**
     * Return News Id
     *
     * @return int|null
     */
    public function getId()
    {
        try {
            if (empty($this->context->getRequest()->getParam('news_id'))) {
                return null;
            }
            return $this->newsRepository->getById(
                $this->context->getRequest()->getParam('news_id')
            )->getId();
        } catch (\Exception $e) {
            $this->logger->info('Generic Block Exception', $e->getMessage());
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

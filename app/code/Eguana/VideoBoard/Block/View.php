<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/6/20
 * Time: 5:14 PM
 */
namespace Eguana\VideoBoard\Block;

use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * class View
 *
 * block for details.phtml
 */
class View extends Template
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var VideoBoardRepositoryInterface
     */
    public $videoBoardRepository;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * VideoBoard constructor.
     * @param Template\Context $context
     * @param StoreManagerInterface $storeManager
     * @param VideoBoardRepositoryInterface $videoBoardRepository
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        VideoBoardRepositoryInterface $videoBoardRepository,
        StoreManagerInterface $storeManager,
        RequestInterface $requestInterface,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->videoBoardRepository = $videoBoardRepository;
        $this->requestInterface = $requestInterface;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * get video board method
     *
     * @return VideoBoard
     */
    public function getVideoBoard()
    {
        /** @var VideoBoard $videoBoard */
        $id = $this->requestInterface->getParam('id');
        $videoBoard = $this->videoBoardRepository->getById($id);
        return $videoBoard;
    }

    /**
     * To set page title and breadcrumb
     * This function will set the page title according to the current video title
     * and it will also set the breadcrumb
     * @return $this|View
     */

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        try {
            $this->pageConfig->getTitle()->set($this->getVideoBoard()->getTitle());
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link' => $this->storeManager->getStore()->getBaseUrl()
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'brand',
                    [
                        'label' => __('Brand'),
                        'title' => __('Brand')
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'videoboard',
                    [
                        'label' => __('How to'),
                        'title' => __('How to'),
                        'link' => $this->storeManager->getStore()->getBaseUrl(). 'videoboard'
                    ]
                );
                if (!empty($this->getVideoBoard()->getData())) {
                    $breadcrumbsBlock->addCrumb(
                        'main_title',
                        [
                            'label' => __($this->getVideoBoard()->getTitle()),
                            'title' => __($this->getVideoBoard()->getTitle())
                        ]
                    );
                }
            }
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return $this;
    }
}

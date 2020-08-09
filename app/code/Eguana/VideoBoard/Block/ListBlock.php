<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/6/20
 * Time: 12:38 PM
 */
namespace Eguana\VideoBoard\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Eguana\VideoBoard\Model\VideoBoard;

/**
 * This class used to add breadcrumbs and title
 *
 * Class ListBlock
 * Eguana\VideoBoard\Block
 */
class ListBlock extends Template implements IdentityInterface
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
     * Index constructor.
     * @param Template\Context $context
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    /**
     * @return array
     */
    public function getIdentities()
    {
        return [VideoBoard::CACHE_TAG];
    }

    /**
     * To set page title and breadcrumb
     * This function will set the page title according to the current video title
     * and it will also set the breadcrumb
     * @return $this|ListBlock
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        try {
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
                    'videoboard',
                    [
                        'label' => __('Brand'),
                        'title' => __('Brand'),
                    ]
                );
                $this->pageConfig->getTitle()->set(__('How To'));
                $breadcrumbsBlock->addCrumb(
                    'main_title',
                    [
                        'label' => __('How to'),
                        'title' => __('How to')
                    ]
                );
            }
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return $this;
    }
}

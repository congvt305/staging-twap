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

use Eguana\VideoBoard\Api\Data\VideoBoardInterface;
use Eguana\VideoBoard\Model\VideoBoard;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * class View
 *
 * block for details.phtml
 */
class View extends AbstractBlock
{
    /**
     * Get video id
     *
     * @return mixed
     */
    public function getVideoBoardId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * get video board method
     *
     * @return VideoBoard
     */
    public function getVideoBoard()
    {
        /** @var VideoBoard $videoBoard */
        $videoBoard = $this->videoBoardRepository->getById($this->getVideoBoardId());
        return $videoBoard;
    }

    /**
     * To filter the content
     * This function will get the content, specially the page builder content and make it renderable at frontend.
     * @param $content
     * @return mixed
     */
    public function contentFiltering($content)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($content);
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
                        'link' => $this->_storeManager->getStore()->getBaseUrl()
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
                        'link' => $this->_storeManager->getStore()->getBaseUrl(). 'videoboard'
                    ]
                );
                if ($this->getVideoBoardId()) {
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

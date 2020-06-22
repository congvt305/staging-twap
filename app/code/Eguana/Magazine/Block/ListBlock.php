<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/18/20
 * Time: 3:34 AM
 */

namespace Eguana\Magazine\Block;

class ListBlock extends AbstractBlock
{
    /**
     * To set page title and breadcrumb
     * This function will set the page title according to the current magazine title
     * and it will also set the breadcrumb
     * @return $this|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $this->pageConfig->getTitle()->set(__('LANEIGE'));

            $breadcrumbsBlock->addCrumb(
                'explore',
                [
                    'label' => __('Magazine'),
                    'title' => __('Magazine')
                ]
            );

        }

        return $this;
    }
}

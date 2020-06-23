<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/18/20
 * Time: 6:48 AM
 */

namespace Eguana\Magazine\Block;

/**
 * class View
 *
 * block for details.phtml
 */
class View extends AbstractBlock
{
    /**
     * Get magazine id
     *
     * @return mixed
     */
    public function getMagazineId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * SHORT DESCRIPTION
     * @return \Eguana\Magazine\Model\Magazine
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMagazine()
    {
        /** @var \Eguana\Magazine\Model\Magazine $magazine */
        $magazine = $this->magazineRepository->getById($this->getMagazineId());
        return $magazine;
    }

    /**
     * To filter the content
     * This function will get the content, specially the page builder content and make it renderable at frontend.
     * @param $content
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */

    public function contentFiltering($content)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($content);
    }

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
            $breadcrumbsBlock->addCrumb(
                'Magazine',
                [
                    'label' => __('Magazine'),
                    'title' => __('Magazine'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl() . 'magazine'
                ]
            );
            if ($this->getMagazineId()) {
                $this->pageConfig->getTitle()->set(__($this->getMagazine()->getTitle()));

                $breadcrumbsBlock->addCrumb(
                    'main_title',
                    [
                        'label' => __($this->getMagazine()->getTitle()),
                        'title' => __($this->getMagazine()->getTitle())
                    ]
                );
            }
        }

        return $this;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     */
    public function getMazineType()
    {
        return $this->getMagazine()->getType();
    }
}

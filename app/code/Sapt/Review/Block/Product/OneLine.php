<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sapt\Review\Block\Product;

/**
 * Detailed Product Reviews
 *
 * @api
 * @since 100.0.2
 */
class OneLine extends \Magento\Review\Block\Product\View
{
    /**
     * Unused class property
     * @var false
     */
    protected $_forceHasOptions = false;

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product ? $product->getId() : null;
    }

    /**
     * Add rate votes
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if ($this->getProductId()) {
            $this->getReviewsCollection()->load()->addRateVotes();
        }

        return parent::_beforeToHtml();
    }

    /**
     * Return review url
     *
     * @param int $id
     * @return string
     */
    public function getReviewUrl($id)
    {
        return $this->getUrl('*/*/view', ['id' => $id]);
    }
}

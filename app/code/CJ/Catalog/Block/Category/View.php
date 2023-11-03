<?php
declare(strict_types=1);

namespace CJ\Catalog\Block\Category;
class View extends \Magento\Catalog\Block\Category\View
{
    const TW_LANEIGE_CATEGORY_ALL = [
            '76' => 'https://tw.laneige.com/skincare/category.html',
            '109' => 'https://tw.laneige.com/skincare/concern.html',
            '130' => 'https://tw.laneige.com/skincare/line.html',
            '166' => 'https://tw.laneige.com/make-up/face.html'
        ];

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        \Magento\Framework\View\Element\AbstractBlock::_prepareLayout();
        $category = $this->getCurrentCategory();
        if ($category) {
            $title = $category->getMetaTitle();
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            }
            $description = $category->getMetaDescription();
            if ($description) {
                $this->pageConfig->setDescription($description);
            }
            $keywords = $category->getMetaKeywords();
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }
            if ($this->_categoryHelper->canUseCanonicalTag()) {
                // customize here to set specific canonical for some url
                $isMatchedUrl = false;
                foreach(self::TW_LANEIGE_CATEGORY_ALL as $id => $url) {
                    if ($category->getEntityId() == $id) {
                        $isMatchedUrl = true;
                        $this->pageConfig->addRemotePageAsset(
                            $url,
                            'canonical',
                            ['attributes' => ['rel' => 'canonical']]
                        );
                        break;
                    }
                }
                if (!$isMatchedUrl) {
                    $this->pageConfig->addRemotePageAsset(
                        $category->getUrl(),
                        'canonical',
                        ['attributes' => ['rel' => 'canonical']]
                    );
                }
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($this->getCurrentCategory()->getName());
            }
        }

        return $this;
    }
}

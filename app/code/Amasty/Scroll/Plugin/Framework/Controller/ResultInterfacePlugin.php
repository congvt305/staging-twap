<?php

declare(strict_types=1);

namespace Amasty\Scroll\Plugin\Framework\Controller;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager;

/**
 * Find last page only after rendering product list block. In other case - fatal with elasticsearch
 */
class ResultInterfacePlugin
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    public function __construct(
        Escaper $escaper,
        UrlInterface $urlBuilder,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
        $this->layout = $layout;
    }

    /**
     * @param ResultInterface $subject
     * @param string $result
     * @param ResponseInterface $response
     *
     * @return string
     */
    public function afterRenderResult(ResultInterface $subject, $result, ResponseInterface $response)
    {
        if ($subject instanceof \Magento\Framework\View\Result\Page) {
            $output = $response->getBody();
            $output = $this->modifyBody($output);
            $response->setBody($output);
        }

        return $result;
    }

    /**
     * @param string $output
     * @return string $output
     */
    public function modifyBody($output)
    {
        $html = $this->getPrevNextLinkContent();
        if ($html) {
            $head = '</head>';
            $output = str_replace($head, $html . $head, $output);
        }

        return $output;
    }

    /**
     * @return string $html
     */
    public function getPrevNextLinkContent()
    {
        $html = '';
        if ($pagerBlock = $this->getPagerBlock()) {
            $lastPage = $pagerBlock->getLastPageNum();
            $currentPage = $pagerBlock->getCurrentPage();

            if ($currentPage > 1) {
                $url = $this->getPageUrl($pagerBlock->getPageVarName(), $currentPage - 1);
                $html .= sprintf($this->getLinkTemplate(), 'prev', $url);
            }

            if ($currentPage < $lastPage) {
                $url = $this->getPageUrl($pagerBlock->getPageVarName(), $currentPage + 1);
                $html .= sprintf($this->getLinkTemplate(), 'next', $url);
            }
        }

        return $html;
    }

    /**
     * @return Pager|null
     */
    protected function getPagerBlock()
    {
        $pagerBlock = null;
        $productListBlock = $this->getCategoryProductListBlock();
        if ($productListBlock) {
            $toolbarBlock = $productListBlock->getToolbarBlock();
            /** @var Pager $pagerBlock */
            $pagerBlock = $toolbarBlock->getChildBlock('product_list_toolbar_pager');
            if ($pagerBlock) {
                $pagerBlock
                    ->setLimit($toolbarBlock->getLimit())
                    ->setAvailableLimit($toolbarBlock->getAvailableLimit())
                    ->setCollection($productListBlock->getLayer()->getProductCollection());
            }
        }

        return $pagerBlock;
    }

    /**
     * @return \Magento\Catalog\Block\Product\ListProduct
     */
    private function getCategoryProductListBlock()
    {
        $productListBlock = $this->layout->getBlock('category.products.list');
        if (!$productListBlock) {
            foreach ($this->layout->getAllBlocks() as $block) {
                if ($block instanceof \Magento\Catalog\Block\Product\ListProduct) {
                    $productListBlock = $block;
                    break;
                }
            }
        }

        return $productListBlock;
    }

    /**
     * @param string $key
     * @param int value
     * @return string
     */
    private function getPageUrl($key, $value)
    {
        $currentUrl = $this->getCurrentUrl();
        $result = preg_replace(
            '/(\W)' . $key . '=\d+/',
            "$1$key=$value",
            $currentUrl,
            -1,
            $count
        );
        if ($value == 1) {
            $result = str_replace($key . '=1&amp;', '', $result); //not last & not single param
            $result = str_replace('&amp;' . $key . '=1', '', $result); //last param
            $result = str_replace('?' . $key . '=1', '', $result); //single param
        } elseif (!$count) {
            $delimiter = (strpos($currentUrl, '?') === false) ? '?' : '&amp;';
            $result .= $delimiter . $key . '=' . $value;
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getCurrentUrl(): string
    {
        $currentUrl = $this->urlBuilder->getCurrentUrl();
        return $this->escaper->escapeUrl($currentUrl);
    }

    /**
     * @return string
     */
    private function getLinkTemplate()
    {
        return '<link rel="%s" href="%s" />' . PHP_EOL;
    }
}

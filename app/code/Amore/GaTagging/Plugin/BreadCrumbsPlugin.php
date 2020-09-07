<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 9/4/20
 * Time: 8:06 AM
 */

namespace Amore\GaTagging\Plugin;


use Magento\Theme\Block\Html\Breadcrumbs;

class BreadCrumbsPlugin
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->registry = $registry;
        $this->request = $request;
    }

    /**
     * @param \Magento\Theme\Block\Html\Breadcrumbs $subject
     * @param $result
     */
    public function afterToHtml(\Magento\Theme\Block\Html\Breadcrumbs $subject, $result)
    {
        $breadCrumbText = "";
        if (!$result) {
            return $this->makeScript($breadCrumbText, $result);
        }

        if ($this->registry->registry('product')) {
            $breadCrumbText = $this->getProductPageBreadCrumbs();
            return $this->makeScript($breadCrumbText, $result);
        }

        $breadCrumbText = str_replace('</li>', '>', $result);
        $breadCrumbText = strip_tags($breadCrumbText);
        $breadCrumbText = str_replace(["\n", ' '], '', $breadCrumbText);
        $breadCrumbText = substr($breadCrumbText, 0, -1);

        return $this->makeScript($breadCrumbText, $result);
    }
    private function getProductPageBreadCrumbs()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('product');
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $product->getCategory();
        $breadCrumbText = __('Home') . '>';
        if ($category) {
            $breadCrumbText .= $category->getName() . '>';
        }
        $breadCrumbText .= $product->getName();
        return $breadCrumbText;
    }
    private function makeScript($breadCrumbText, $result)
    {
        $apScript = '<script>var AP_DATA_BREAD = ' . '"' . $breadCrumbText . '"'.'; </script>';
        return $result . $apScript;
    }
}
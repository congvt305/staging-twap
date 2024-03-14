<?php
declare(strict_types=1);

namespace CJ\Cms\Helper;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Canonical
 *
 */
class Canonical extends AbstractHelper
{
    /**
     * @var Page
     */
    protected $cmsPage;

    /**
     * Canonical constructor.
     * @param Context $context
     * @param Page    $cmsPage
     */
    public function __construct(
        Context $context,
        Page $cmsPage,
        RequestInterface $request
    ) {
        $this->cmsPage = $cmsPage;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * This method is used in XML layout.
     * @return string
     */
    public function getCanonicalForAllCmsPages(): string
    {
        if ($this->cmsPage->getId()) {
            return $this->createLink(
                $this->scopeConfig->getValue('web/secure/base_url', ScopeInterface::SCOPE_STORE) . preg_replace('/^\//', '', $this->request->getRequestUri())
            );
        }

        return '';
    }

    /**
     * @param $url
     * @return string
     */
    protected function createLink($url): string
    {
        return '<link rel="canonical" href="' . $url . '" />';

    }
}

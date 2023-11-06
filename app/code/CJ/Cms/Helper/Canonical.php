<?php
declare(strict_types=1);

namespace CJ\Cms\Helper;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Canonical
 * @package [Namespace]\[Module]\Helper
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
        Page $cmsPage
    ) {
        $this->cmsPage = $cmsPage;
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
                $this->scopeConfig->getValue('web/secure/base_url') . $this->cmsPage->getIdentifier()
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

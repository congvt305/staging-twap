<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Plugin\Cms\Helper\Page;

use Amasty\SeoToolkitLite\Model\RegistryConstants;
use Magento\Cms\Helper\Page;
use Magento\Cms\Model\Page as PageModel;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Store\Model\StoreManagerInterface;

class AddCmsRobotsPlugin
{
    /**
     * @var PageModel
     */
    private $page;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        PageModel $page,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        StoreManagerInterface $storeManager
    ) {
        $this->page = $page;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Page $subject
     * @param ResultPage|bool $result
     * @return ResultPage|bool
     */
    public function afterPrepareResultPage(Page $subject, $result)
    {
        if ($result !== false) {
            $this->addCanonical($result);
            $this->addRobots($result);
        }

        return $result;
    }

    private function addCanonical(ResultPage $page): void
    {
        $pageConfig = $page->getConfig();
        $canonical = $this->page->getCanonical();
        if ($canonical) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $pageConfig->addRemotePageAsset(
                $this->escaper->escapeUrl($baseUrl . ltrim($canonical, '/')),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
    }

    private function addRobots(ResultPage $page): void
    {
        $robots = $this->page->getRobots();
        if ($robots !== RegistryConstants::DEFAULT_ROBOTS) {
            $page->getConfig()->setRobots($robots);
        }
    }
}

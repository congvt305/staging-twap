<?php

namespace Amasty\Scroll\Plugin\Ajax;

use Amasty\Scroll\Helper\Data;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Result\Page;
use Magento\PageCache\Model\Config as FpcConfig;

class AjaxAbstract
{
    public const OSN_CONFIG = 'amasty.xnotif.config';

    public const QUICKVIEW_CONFIG = 'amasty.quickview.config';

    /**
     * List of block names that contain a product list.
     * For compatibility with third-party themes can be extended through the constructor.
     *
     * @var array
     */
    private $productBlocks = [
        'category.products.list',
        'search_result_list'
    ];

    /**
     * List of block names that contain a additional configs for product list. E.g. initialization scripts
     * For compatibility with third-party extensions can be extended through the constructor.
     *
     * @var array
     */
    private $additionalConfigBlocks = [
        self::OSN_CONFIG,
        self::QUICKVIEW_CONFIG
    ];

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var IdentityInterface[]
     */
    private $cachebleBlocks = [];

    /**
     * @var FpcConfig
     */
    private $fpcCacheConfig;

    public function __construct(
        Data $helper,
        Http $request,
        RawFactory $resultRawFactory,
        UrlInterface $url,
        UrlHelper $urlHelper,
        Response $response,
        EncoderInterface $jsonEncoder,
        DataObjectFactory $dataObjectFactory,
        ManagerInterface $eventManager,
        FpcConfig $fpcCacheConfig,
        array $productBlocks = [],
        array $additionalConfigBlocks = []
    ) {
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->request = $request;
        $this->response = $response;
        $this->urlHelper = $urlHelper;
        $this->url = $url;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $eventManager;
        $this->fpcCacheConfig = $fpcCacheConfig;
        $this->productBlocks = array_unique(array_merge($this->productBlocks, $productBlocks));
        $this->additionalConfigBlocks = array_merge($this->additionalConfigBlocks, $additionalConfigBlocks);
    }

    /**
     * @param
     *
     * @return bool
     */
    protected function isAjax()
    {
        $isAjax = $this->request->isAjax();
        $isScroll = $this->request->getParam('is_scroll');

        return $this->helper->isEnabled() && $isAjax && $isScroll;
    }

    /**
     * @param string $blockName
     *
     * @return BlockInterface|null
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getBlock(string $blockName): ?BlockInterface
    {
        $block = $this->getPage()->getLayout()->getBlock($blockName);

        if ($block && $block instanceof IdentityInterface) {
            $this->cachebleBlocks[$blockName] = $block;
        }

        return $block ?: null;
    }

    /**
     * @param Page $page
     */
    protected function setPage(Page $page)
    {
        $this->page = $page;
    }

    /**
     * @return BlockInterface|null
     */
    private function getProductBlock(): ?BlockInterface
    {
        foreach ($this->productBlocks as $productBlock) {
            if ($block = $this->getBlock($productBlock)) {
                return $block;
            }
        }

        return null;
    }

    /**
     * @return Page
     * @throws NoSuchEntityException
     */
    protected function getPage(): Page
    {
        if ($this->page === null) {
            throw new NoSuchEntityException(__('Page has not been set.'));
        }
        
        return $this->page;
    }

    /**
     * @return array
     */
    protected function getAjaxResponseData(): array
    {
        $currentPage = (int)$this->request->getParam('p', 1);
        $products = $this->getProductBlock();

        if ($products) {
            $html = $this->getAdditionalConfigs();
            $html .= $products->toHtml();

            //fix bug with multiple adding to cart
            $search = '[data-role=tocart-form]';
            $replace = ".amscroll-pages[amscroll-page='" . $currentPage . "'] " . $search;
            $html = str_replace($search, $replace, $html);

            $this->replaceUencFromHtml($html);
            $html = $this->applyEventChanges($html);
        }

        return [
            'categoryProducts' => $html ?? '',
            'currentPage'      => $currentPage
        ];
    }

    /**
     * Compatibility with Google Page SpeedOptimizer
     * @param string $html
     *
     * @return string|mixed
     */
    protected function applyEventChanges(string $html)
    {
        $dataObject = $this->dataObjectFactory->create(
            [
                'data' => [
                    'page' => $html,
                    'pageType' => 'catalog_category_view'
                ]
            ]
        );
        $this->eventManager->dispatch('amoptimizer_process_ajax_page', ['data' => $dataObject]);
        $html = $dataObject->getData('page');

        return $html;
    }

    /**
     * replace uenc for correct redirect
     *
     * @param $html
     */
    private function replaceUencFromHtml(&$html)
    {
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $this->url->getCurrentUrl();
        $refererUrl = $this->urlHelper->removeRequestParam($refererUrl, 'is_scroll');

        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
        $html = str_replace($currentUenc, $newUenc, $html);
    }

    /**
     * @return string
     */
    private function getAdditionalConfigs(): string
    {
        $html = '';

        foreach ($this->additionalConfigBlocks as $blockName) {
            $html .= $this->getBlockHtml($blockName);
        }

        return $html;
    }

    /**
     * @param string $blockName
     *
     * @return string
     */
    private function getBlockHtml(string $blockName)
    {
        $block = $this->getBlock($blockName);

        return $block ? $block->toHtml() : '';
    }

    /**
     * @param ResultInterface|HttpInterface $response
     */
    public function updateHeaders($response)
    {
        $response->setHeader('Content-Type', 'application/json');
        $this->addXMagentoTags($response);
    }

    /**
     * @param ResultInterface|HttpInterface $response
     */
    private function addXMagentoTags($response)
    {
        $tags = $this->getXMagentoTags();

        if (!empty($tags)) {
            $response->setHeader('X-Magento-Tags', implode(',', $tags));
        }
    }

    /**
     * @return string[]
     */
    private function getXMagentoTags(): array
    {
        $tags = [];

        if ($this->fpcCacheConfig->isEnabled()) {
            $isVarnish = $this->fpcCacheConfig->getType() === FpcConfig::VARNISH;
            $tags = array_reduce($this->cachebleBlocks, function ($current, IdentityInterface $block) use ($isVarnish) {
                $isEsiBlock = $block->getTtl() > 0;

                return $isVarnish && $isEsiBlock ? $current : array_merge($current, (array)$block->getIdentities());
            }, $tags);
        }

        return array_unique($tags);
    }
}

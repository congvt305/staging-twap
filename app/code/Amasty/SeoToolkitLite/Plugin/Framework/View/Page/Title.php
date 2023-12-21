<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

namespace Amasty\SeoToolkitLite\Plugin\Framework\View\Page;

use Amasty\SeoToolkitLite\Helper\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Page\Title as NativeTitle;

class Title
{
    public const ALL_PRODUCTS_PARAM = 'all';

    /**
     * @var string
     */
    protected $_pageVarName = 'p';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Config $config,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @param NativeTitle $subject
     * @param $result
     * @return string
     */
    public function afterGet(
        NativeTitle $subject,
        $result
    ) {
        if ($this->config->isAddPageToMetaTitleEnabled()) {
            $page = (int) $this->request->getParam($this->_pageVarName, false);
            if ($page) {
                $result .= __(' | Page %1', $page);
            }
        }

        return $result;
    }
}

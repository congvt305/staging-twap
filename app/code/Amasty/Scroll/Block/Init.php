<?php

namespace Amasty\Scroll\Block;

use Amasty\Scroll\Helper\Data;
use Amasty\Scroll\Model\Source\Loading;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;

class Init extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var Http
     */
    private $request;

    public function __construct(
        Context $context,
        Data $helper,
        EncoderInterface $jsonEncoder,
        Http $request,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->request = $request;
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * @return string
     */
    public function getProductsBlockSelector()
    {
        $originSelectors = $this->helper->getModuleConfig('advanced/product_container_group');

        //compatibility with Amasty_PromoBanners
        $selectors = ($originSelectors === null) ? ['.products.wrapper'] : explode(',', $originSelectors);
        foreach ($selectors as &$selector) {
            $selector = rtrim($selector);
            $selector .= ':not(.amasty-banners)';
        }

        return implode(',', $selectors);
    }

    /**
     * @return string
     */
    public function getFooterSelector(): string
    {
        return (string)$this->helper->getModuleConfig('advanced/footer_selector');
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        $currentPage = (int)$this->request->getParam('p', 1);
        $actionMode = $this->helper->getModuleConfig('general/loading');
        $iconUrl = $this->getViewFileUrl((string)$this->helper->getModuleConfig('general/loader'));

        $params = [
            'product_container' => $this->getProductsBlockSelector(),
            'product_link' => $this->helper->getModuleConfig('advanced/product_link'),
            'loadingImage' => $iconUrl,
            'pageNumbers' => $this->helper->getModuleConfig('general/page_numbers'),
            'pageNumberContent' => __('Page #'),
            'pageNumberStyle' => $this->helper->getModuleConfig('general/page_number_style'),
            'buttonColor' => $this->helper->getModuleConfig('button/color'),
            'buttonColorPressed' => $this->helper->getModuleConfig('button/color_pressed'),
            'loadingafterTextButton' => $this->helper->getModuleConfig('button/label_after'),
            'loadingbeforeTextButton' => $this->helper->getModuleConfig('button/label_before'),
            'backToTop' => $this->helper->getModuleConfig('info'),
            'backToTopText' => __('Back to Top'),
            'current_page' => $currentPage,
            'footerSelector' => $this->getFooterSelector(),
        ];

        if ($actionMode == Loading::COMBINED) {
            $pagesBeforeButton = $this->helper->getModuleConfig('general/num_pages_before_button');
            $params['origActionMode'] = $actionMode;
            $actionMode = Loading::AUTO;
            $params['pages_before_button'] = (int)$pagesBeforeButton ?: Loading::DEFAULT_COMBINED_VALUE;
        } elseif ($actionMode == Loading::COMBINED_BUTTON_AUTO) {
            $pagesBeforeAuto = $this->helper->getModuleConfig('general/num_pages_before_auto');
            $params['origActionMode'] = $actionMode;
            $actionMode = Loading::BUTTON;
            $params['pages_before_button'] = $pagesBeforeAuto ?? Loading::DEFAULT_COMBINED_BUTTON_AUTO_VALUE;
        }

        $params['actionMode'] = $actionMode;

        return $this->jsonEncoder->encode($params);
    }
}

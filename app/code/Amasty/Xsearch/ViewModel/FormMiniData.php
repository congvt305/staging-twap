<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Advanced Search Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\Xsearch\ViewModel;

use Amasty\Xsearch\Model\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class FormMiniData implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $urlHelper;

    /**
     * @var Config
     */
    private $moduleConfigProvider;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        Data $urlHelper,
        SerializerInterface $jsonSerializer,
        Config $moduleConfigProvider,
        UrlInterface $urlBuilder
    ) {
        $this->urlHelper = $urlHelper;
        $this->moduleConfigProvider = $moduleConfigProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param string|null $url
     * @return string
     */
    public function getOptions(?string $url = null): string
    {
        return $this->jsonSerializer->serialize([
            'url' => $this->urlBuilder->getUrl('amasty_xsearch/autocomplete/index'),
            'url_result' => $this->urlBuilder->getUrl('catalogsearch/result'),
            'url_popular' => $this->urlBuilder->getUrl('search/term/popular'),
            'isDynamicWidth' => $this->moduleConfigProvider->isDynamicWidth(),
            'isProductBlockEnabled' => $this->moduleConfigProvider->isProductBlockEnabled(),
            'width' => $this->moduleConfigProvider->getPopupWidth(),
            'displaySearchButton' => $this->isDisplaySearchButton(),
            'fullWidth' => $this->moduleConfigProvider->isFullScreenEnabled(),
            'minChars' => $this->getMinChars(),
            'delay' => $this->moduleConfigProvider->getDelay() * 1000,
            'currentUrlEncoded' => $this->getCurrentUrlEncoded($url),
            'color_settings' => $this->moduleConfigProvider->getColorSettings(),
            'popup_display' => $this->moduleConfigProvider->getPopupViewType()
        ]);
    }

    /**
     * @return array
     */
    public function getColorSettings(): array
    {
        return $this->moduleConfigProvider->getColorSettings();
    }

    /**
     * @return int
     */
    public function getMinChars(): int
    {
        return $this->moduleConfigProvider->getMinChars();
    }

    /**
     * @return bool
     */
    public function isDisplaySearchButton(): bool
    {
        return $this->moduleConfigProvider->isDisplaySearchButton();
    }

    /**
     * @param string|null $url
     * @return string
     */
    public function getCurrentUrlEncoded(?string $url): string
    {
        return $this->urlHelper->getEncodedUrl($url);
    }

    /**
     * @return bool
     */
    public function isFullScreenEnabled(): bool
    {
        return $this->moduleConfigProvider->isFullScreenEnabled();
    }
}

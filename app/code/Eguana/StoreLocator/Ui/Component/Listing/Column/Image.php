<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-13
 * Time: 오전 10:00
 */

namespace Eguana\StoreLocator\Ui\Component\Listing\Column;

use Exception;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Eguana\StoreLocator\Helper\ConfigData;
use Psr\Log\LoggerInterface;

/**
 * Get default image for store
 *
 * Class Image
 *  Eguana\StoreLocator\Ui\Component\Listing\Column
 */
class Image extends Column
{
    const ALT_FIELD = 'image';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var ConfigData
     */
    private $storesHelper;

    private $logger;

    /**
     * Image constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ImageHelper $imageHelper
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param ConfigData $storesHelper
     * @param LoggerInterface $logger
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ImageHelper $imageHelper,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        ConfigData $storesHelper,
        LoggerInterface $logger,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->storesHelper = $storesHelper;
        $this->logger = $logger;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                try {
                    if (isset($item[$fieldName])) {
                        $url = $this->storeManager->getStore()->getBaseUrl(
                            UrlInterface::URL_TYPE_MEDIA
                        ) . $this->storesHelper->getMediaPath() . '/' . $item[$fieldName];
                    } else {
                        $url = $this->storeManager->getStore()->getBaseUrl(
                            UrlInterface::URL_TYPE_STATIC
                        ) . 'frontend/Eguana/nc/en_US/Eguana_StoreLocator/images/store_default.jpg';
                    }
                    $item[$fieldName . '_src'] = $url;
                    $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                    $item[$fieldName . '_orig_src'] = $url;

                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}

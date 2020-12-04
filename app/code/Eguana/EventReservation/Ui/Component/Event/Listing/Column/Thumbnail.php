<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/10/20
 * Time: 6:33 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\Component\Event\Listing\Column;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Psr\Log\LoggerInterface;

/**
 * To Retrive Thumbnail
 *
 * Class Thumbnail
 */
class Thumbnail extends Column
{
    /**#@+
     * Constant for alt field of image
     */
    const ALT_FIELD = 'title';
    /**#@-*/

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->logger       = $logger;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) : array
    {
        try {
            if (isset($dataSource['data']['items'])) {
                $fieldName = $this->getData('name');
                foreach ($dataSource['data']['items'] as & $item) {
                    if (!array_key_exists($fieldName, $item)) {
                        continue;
                    }
                    $url = '';
                    if ($item[$fieldName] != '') {
                        $url = $this->storeManager->getStore()->getBaseUrl(
                            UrlInterface::URL_TYPE_MEDIA
                        ) . $item[$fieldName];
                    }
                    $item[$fieldName . '_src'] = $url;
                    $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->info($e->getMessage());
        }

        return $dataSource;
    }

    /**
     * Get alt field value
     *
     * @param array $row
     * @return mixed|null
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}

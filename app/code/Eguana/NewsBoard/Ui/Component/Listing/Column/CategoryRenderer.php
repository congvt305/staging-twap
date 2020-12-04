<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 6:55 PM
 */
namespace Eguana\NewsBoard\Ui\Component\Listing\Column;

use Eguana\NewsBoard\Model\NewsConfiguration\NewsConfiguration;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Psr\Log\LoggerInterface;

/**
 * Class for displaying Store views in Grid
 *
 * Class StoreRenderer
 *
 */
class CategoryRenderer extends Column
{
    /**
     * @var NewsConfiguration
     */
    private $newsConfiguration;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CategoryRenderer constructor.
     * @param ContextInterface $context
     * @param StoreManagerInterface $storeManager
     * @param UiComponentFactory $uiComponentFactory
     * @param NewsConfiguration $newsConfiguration
     * @param LoggerInterface $logger
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        UiComponentFactory $uiComponentFactory,
        NewsConfiguration $newsConfiguration,
        LoggerInterface $logger,
        array $components = [],
        array $data = []
    ) {
        $this->newsConfiguration = $newsConfiguration;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $item['category'];
                if (isset($item['category'])) {
                    $item['category'] = $this->prepareItem($name);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Prepare Store views data source
     *
     * @param array $name
     * @return string
     */
    protected function prepareItem(array $name)
    {
        $content = '';
        try {
            foreach ($name as $value) {
                $categoryStoreId = explode('.', $value);
                $categories = $this->newsConfiguration->getCategory('category', $categoryStoreId[0]);
                if (isset($categories)) {
                    $content .= "<b>" . $this->storeManager->getStore($categoryStoreId[0])->getName() . "</b><br/>";
                    if (isset($categories[$categoryStoreId[1]])) {
                        $content .= str_repeat('&nbsp;', 4) . $categories[$categoryStoreId[1]] . "<br/>";
                    } else {
                        $del= "Deleted";
                        $content .= str_repeat('&nbsp;', 4) . $del . "<br/>";
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $content;
    }
}

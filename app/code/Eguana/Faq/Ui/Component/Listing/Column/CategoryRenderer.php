<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Ui\Component\Listing\Column;

use Eguana\Faq\Model\FaqConfiguration\FaqConfiguration;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Eguana\Faq\Model\Source\Category as CategoryOption;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Auth\Session;
use Psr\Log\LoggerInterface;

/**
 * Class CategoryRenderer
 * This class is used to show store vise categories in admin grid
 */
class CategoryRenderer extends Column
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var CategoryOption
     */
    private $categoryOption;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var FaqConfiguration
     */
    private $faqConfiguration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param CategoryOption $categoryOption
     * @param Session $session
     * @param FaqConfiguration $faqConfiguration
     * @param LoggerInterface $logger
     * @param array $components
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        CategoryOption $categoryOption,
        Session $session,
        FaqConfiguration $faqConfiguration,
        LoggerInterface $logger,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->categoryOption = $categoryOption;
        $this->session = $session;
        $this->faqConfiguration = $faqConfiguration;
        $this->logger = $logger;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $item['category'];
                if (isset($item['category'])) {
                    $item['category'] = $this->prepareItem($name);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get the faq related category value
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
                $categories = $this->faqConfiguration->getCategory($categoryStoreId[0]);
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

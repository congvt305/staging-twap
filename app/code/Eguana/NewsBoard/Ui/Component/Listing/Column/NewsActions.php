<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 2:30 PM
 */

namespace Eguana\NewsBoard\Ui\Component\Listing\Column;

use Magento\Cms\ViewModel\Page\Grid\UrlBuilder;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used for edit and delete actions functionality
 *
 * Class NewsActions
 */
class NewsActions extends Column
{
    /**
     * Url path for edit
     */
    const URL_PATH_EDIT = 'news/manage/edit';
    /**
     * Url path for delete
     */
    const URL_PATH_DELETE = 'news/manage/delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var NewsRepositoryInterface
     */
    private $newsRepository;

    /**
     * @var UrlBuilder
     */
    private $scopeUrlBuilder;

    /**
     * NewsActions constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param UrlBuilder $scopeUrlBuilder
     * @param NewsRepositoryInterface $newsRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        UrlBuilder $scopeUrlBuilder,
        NewsRepositoryInterface $newsRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeUrlBuilder = $scopeUrlBuilder;
        $this->newsRepository = $newsRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['news_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'news_id' => $item['news_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'news_id' => $item['news_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' =>__('Delete "${ $.$data.title }"'),
                                'message' =>__('Are you sure you wan\'t to delete a "${ $.$data.title }" record?')
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}

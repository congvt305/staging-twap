<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 6:24 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Ui\Component\Listing\Column;

use Magento\Cms\ViewModel\Page\Grid\UrlBuilder;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Url;

/**
 * Redemption Actions in Listing
 *
 * Class RedemptionActions
 */
class RedemptionActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'redemption/redemption/edit';
    const URL_PATH_DELETE = 'redemption/redemption/delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlBuilder
     */
    private $scopeUrlBuilder;

    /**
     * @var Url
     */
    private $url;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param UrlBuilder $scopeUrlBuilder
     * @param Url $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        UrlBuilder $scopeUrlBuilder,
        Url $url,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->scopeUrlBuilder = $scopeUrlBuilder;
        $this->url = $url;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) : array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['redemption_id'])) {
                    $title = $this->escaper->escapeHtml($item['title']);
                    $href = $this->url->getUrl(
                        $item['identifier'],
                        [
                            '_scope' => isset($item['_first_store_id']) ? $item['_first_store_id'] : null,
                            '_nosid' => true
                        ]
                    );
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'redemption_id' => $item['redemption_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'redemption_id' => $item['redemption_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete %1', $title),
                                'message' => __('Are you sure you want to delete a %1 record?', $title),
                            ]
                        ],
                        'view' => [
                            'target' => "_blank",
                            'href' => $href,
                            'label' => __('View')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}

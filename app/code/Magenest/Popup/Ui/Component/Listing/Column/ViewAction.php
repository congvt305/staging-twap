<?php
namespace Magenest\Popup\Ui\Component\Listing\Column;

use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;

class ViewAction extends Column
{
    /** @var UrlInterface */
    protected $_urlBuilder;

    /** @var Escaper */
    private $escaper;

    /** @var Url */
    protected $frontendUrl;

    /**
     * ViewAction constructor.
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param Url $frontendUrl
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Escaper $escaper,
        Url $frontendUrl,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        $components = [],
        $data = []
    ) {
        $this->escaper = $escaper;
        $this->_urlBuilder = $urlBuilder;
        $this->frontendUrl = $frontendUrl;
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
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
                $title = $this->getEscaper()->escapeHtml($item['template_name']);
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->_urlBuilder->getUrl(
                        'magenest_popup/template/edit',
                        ['id' => $item['template_id'], 'store' => $storeId]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];
                $item[$this->getData('name')]['delete'] = [
                    'href' => $this->_urlBuilder->getUrl(
                        'magenest_popup/template/delete',
                        ['id' => $item['template_id'], 'store' => $storeId]
                    ),
                    'label' => __('Delete'),
                    'hidden' => false,
                    'confirm' => [
                        'title' => __('Delete %1', $title),
                        'message' => __('Are you sure you want to delete a %1 record?', $title)
                    ],
                ];
                $item[$this->getData('name')]['preview'] = [
                    'href' => $this->frontendUrl->getUrl(
                        'magenest_popup/template/preview',
                        ['id' => $item['template_id'], 'store' => $storeId]
                    ),
                    'label' => __('Preview'),
                    'hidden' => false,
                    'target' => '_blank'
                ];
            }
        }
        return $dataSource;
    }

    /**
     * Get instance of escaper
     *
     * @return Escaper
     */
    private function getEscaper()
    {
        return $this->escaper;
    }
}

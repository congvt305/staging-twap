<?php
namespace Magenest\Popup\Ui\Component\Listing\Column\Popup;

use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class ViewAction extends \Magento\Ui\Component\Listing\Columns\Column
{
    /** @var UrlInterface */
    protected $_urlBuilder;

    /** @var Url */
    protected $frontendUrl;

    /** @var Escaper */
    private $escaper;

    /**
     * ViewAction constructor.
     * @param UrlInterface $urlBuilder
     * @param Url $frontendUrl
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Url $frontendUrl,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        $components = [],
        $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->frontendUrl = $frontendUrl;
        $this->escaper = $escaper;
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
                $title = $this->escaper->escapeHtml($item['popup_name']);
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->_urlBuilder->getUrl(
                        'magenest_popup/popup/edit',
                        ['id' => $item['popup_id'], 'store' => $storeId]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];
                $item[$this->getData('name')]['preview'] = [
                    'href' => $this->frontendUrl->getUrl(
                        'magenest_popup/popup/preview',
                        ['popup_id' => $item['popup_id'], 'store' => $storeId]
                    ),
                    'label' => __('Preview'),
                    'hidden' => false,
                    'target' => '_blank'
                ];
                $item[$this->getData('name')]['delete'] = [
                    'href' => $this->_urlBuilder->getUrl(
                        'magenest_popup/popup/delete',
                        ['id' => $item['popup_id'], 'store' => $storeId]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete %1', $title),
                        'message' => __('Are you sure you want to delete a %1 record?', $title)
                    ],
                    'hidden' => false,
                ];
            }
        }
        return $dataSource;
    }
}

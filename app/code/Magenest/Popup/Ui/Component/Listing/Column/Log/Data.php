<?php
namespace Magenest\Popup\Ui\Component\Listing\Column\Log;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Data extends Column
{
    /** @var Json */
    protected $json;

    /**
     * Data constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Json $json
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Json $json,
        $components = [],
        $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->json = $json;
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
                $content = $this->json->unserialize($item['content'] ?? 'null');
                if (is_array($content)) {
                    $result = '';
                    $count = 0;
                    foreach ($content as $raw) {
                        if ($count == 0) {
                            $count++;
                            continue;
                        }
                        if (isset($raw['name'])) {
                            $result .= $raw['name'] . ": " . $raw['value'] . "| ";
                        }
                    }
                    $item['content'] = $result != '' ? $result : $content;
                }
            }
        }
        return $dataSource;
    }
}

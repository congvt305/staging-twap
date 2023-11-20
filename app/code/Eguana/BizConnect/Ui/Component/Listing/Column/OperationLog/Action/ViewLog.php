<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 4:36 PM
 *
 */

namespace Eguana\BizConnect\Ui\Component\Listing\Column\OperationLog\Action;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class ViewLog extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }


    /**
     * @param array $dataSource
     *
     * @return array|void
     */
    public function prepareDataSource(array $dataSource)
    {
        $items = [];
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as $item) {
                $detailsUrl = $this->urlBuilder->getUrl('eguana_bizconnect/operation/log_details', ['id' => $item['id']]);

                $items[] = [
                    "id_field_name" => $item["id_field_name"],
                    "id"            => $item["id"],
                    "status"        => $item["status"],
                    "topic_name"    => $item["topic_name"],
                    "start_time"    => $item["start_time"],
                    "to"            => $item["to"],
                    "direction"     => $item["direction"],
                    "orig_data"     => $item["orig_data"],
                    $fieldName.'_html'        => "<button class='button'><span>View Log</span></button>",
                    $fieldName.'_title'       => $item['topic_name'],
                    $fieldName.'_details_url' => $detailsUrl,
                ];
            }
        }

        return [
            'data' => [
                'items'         => $items,
                'totalRecords'  => $dataSource['data']['totalRecords'],
            ],
        ];

    }


}

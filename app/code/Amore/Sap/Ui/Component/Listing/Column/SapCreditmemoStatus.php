<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-05
 * Time: 오전 11:51
 */

namespace Amore\Sap\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class SapCreditmemoStatus extends Column
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * SapCreditmemoStatus constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        array $components = [],
        array $data = []
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] =
                        $this->getLabelForSapSendStatus($item[$this->getData('name')]);
                }
            }
        }

        return $dataSource;
    }

    public function getLabelForSapSendStatus($value)
    {
        if (is_null($value)) {
            return '';
        }

        switch ($value) {
            case 0:
                $label = "Error Before Send";
                break;
            case 1:
                $label = "Success";
                break;
            case 2:
                $label = "Fail";
                break;
            case 3:
                $label = "Resend Success";
                break;
            default:
                $label = '';
        }
        return $label;
    }
}

<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    const ROUTE_PATH = 'amxmlsitemap/sitemap/';

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getName();
                $id = (int) $item['sitemap_id'];
                $item[$name] = [
                    'generate' => $this->getActionItem($id, 'generate', 'Generate'),
                    'duplicate' => $this->getActionItem($id, 'duplicate', 'Duplicate'),
                    'edit' => $this->getActionItem($id, 'edit', 'Edit')
                ];
            }
        }

        return $dataSource;
    }

    private function getActionItem(
        int $id,
        string $path,
        string $label,
        bool $isHidden = false
    ): array {
        return [
            'href' => $this->context->getUrl(self::ROUTE_PATH . $path, ['sitemap_id' => $id]),
            'label' => __($label),
            'hidden' => $isHidden
        ];
    }
}

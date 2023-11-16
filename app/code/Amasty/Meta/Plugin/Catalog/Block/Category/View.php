<?php
namespace Amasty\Meta\Plugin\Catalog\Block\Category;

class View
{

    /**
     * @var \Amasty\Meta\Helper\Data
     */
    private $data;

    public function __construct(
        \Amasty\Meta\Helper\Data $data
    ) {
        $this->data = $data;
    }

    public function afterGetProductListHtml(
        $subject,
        $html
    ) {
        $textAfter = $this->data->getReplaceData('after_product_text');
        if ($textAfter) {
            $html =  $html . $textAfter;
        }

        return $html;
    }
}

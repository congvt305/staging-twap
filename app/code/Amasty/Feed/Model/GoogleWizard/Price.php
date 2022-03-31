<?php

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Price extends Element
{
    /**
     * @var string
     */
    protected $type = 'attribute';

    /**
     * @var string
     */
    protected $tag = 'g:price';

    /**
     * @var string
     */
    protected $format = 'price';

    /**
     * @var string
     */
    protected $value = ExportProduct::PREFIX_PRICE_ATTRIBUTE . '|final_price';

    /**
     * @var string
     */
    protected $name = 'price';

    /**
     * @var string
     */
    protected $description = 'Price of the item';

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var string
     */
    protected $template
        = '<:tag>{attribute=":value" format=":format" parent=":parent" optional=":optional" modify=":modify"}</:tag>'
        . PHP_EOL;
}

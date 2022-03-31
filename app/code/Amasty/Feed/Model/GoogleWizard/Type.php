<?php

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Type extends Element
{
    /**
     * @var string
     */
    protected $type = 'attribute';

    /**
     * @var string
     */
    protected $tag = 'g:product_type';

    /**
     * @var string
     */
    protected $modify = 'html_escape';

    /**
     * @var string
     */
    protected $value = ExportProduct::PREFIX_CATEGORY_ATTRIBUTE . '|category';

    /**
     * @var string
     */
    protected $name = 'product type';

    /**
     * @var string
     */
    protected $description = 'Your category of the item';
}

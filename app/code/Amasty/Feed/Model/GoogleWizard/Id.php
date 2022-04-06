<?php

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

class Id extends Element
{
    /**
     * @var string
     */
    protected $type = 'attribute';

    /**
     * @var string
     */
    protected $tag = 'g:id';

    /**
     * @var int
     */
    protected $limit = 50;

    /**
     * @var string
     */
    protected $modify = 'html_escape';

    /**
     * @var string
     */
    protected $value = ExportProduct::PREFIX_BASIC_ATTRIBUTE . '|sku';

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var string
     */
    protected $name = 'id';

    /**
     * @var string
     */
    protected $description = 'An identifier of the item';
}

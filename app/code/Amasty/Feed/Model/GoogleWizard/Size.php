<?php

namespace Amasty\Feed\Model\GoogleWizard;

class Size extends Element
{
    /**
     * @var string
     */
    protected $type = 'attribute';

    /**
     * @var string
     */
    protected $tag = 'g:size';

    /**
     * @var string
     */
    protected $modify = 'html_escape';

    /**
     * @var string
     */
    protected $name = 'size';

    /**
     * @var string
     */
    protected $description = 'Size of the item';

    /**
     * @var int
     */
    protected $limit = 100;
}

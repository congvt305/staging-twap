<?php

namespace Amasty\Feed\Model\GoogleWizard;

class Gender extends Element
{
    /**
     * @var string
     */
    protected $type = 'attribute';

    /**
     * @var string
     */
    protected $tag = 'g:gender';

    /**
     * @var string
     */
    protected $modify = 'html_escape';

    /**
     * @var string
     */
    protected $name = 'gender';

    /**
     * @var string
     */
    protected $description = 'Gender of the item';
}

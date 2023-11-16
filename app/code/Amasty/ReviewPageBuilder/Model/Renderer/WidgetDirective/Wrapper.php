<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty Advanced Reviews PageBuilder for Magento 2 (System)
*/

declare(strict_types=1);

namespace Amasty\ReviewPageBuilder\Model\Renderer\WidgetDirective;

class Wrapper extends \Amasty\AdvancedReview\Model\Di\Wrapper
{
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManagerInterface)
    {
        parent::__construct($objectManagerInterface, 'Magento\PageBuilder\Model\Stage\Renderer\WidgetDirective');
    }
}

<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty Advanced Reviews PageBuilder for Magento 2 (System)
*/

declare(strict_types=1);

namespace Amasty\ReviewPageBuilder\Model\Renderer;

use Psr\Log\LoggerInterface;

class Widget implements \Magento\PageBuilder\Model\Stage\RendererInterface
{
    /**
     * @var \Amasty\Blog\Model\Di\Wrapper
     */
    private $widgetDirectiveRenderer;

    /**
     * @var LoggerInterface
     */
    private $loggerInterface;

    public function __construct(
        LoggerInterface $loggerInterface,
        \Amasty\ReviewPageBuilder\Model\Renderer\WidgetDirective\Wrapper $widgetDirectiveRenderer
    ) {
        $this->widgetDirectiveRenderer = $widgetDirectiveRenderer;
        $this->loggerInterface = $loggerInterface;
    }

    /**
     * Render a state object for the specified block for the stage preview
     *
     * @param array $params
     * @return array
     */
    public function render(array $params): array
    {
        $directiveResult = $this->widgetDirectiveRenderer->render($params);
        $result['content'] = isset($directiveResult['content']) ? $directiveResult['content'] : '';

        return $result;
    }
}

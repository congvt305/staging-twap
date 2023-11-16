<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Block\Adminhtml\Edit;

use Amasty\SeoToolkitLite\Api\Data\RedirectInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $redirectId = $this->request->getParam(RedirectInterface::REDIRECT_ID);
        if ($redirectId) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf(
                    "deleteConfirm('%s','%s')",
                    __('Are you sure you want to delete redirect?'),
                    $this->urlBuilder->getUrl(
                        '*/*/delete',
                        [RedirectInterface::REDIRECT_ID => $redirectId]
                    )
                ),
                'sort_order' => 20,
            ];
        }

        return $data;
    }
}

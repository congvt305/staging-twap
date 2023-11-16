<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Controller;

use Amasty\SeoToolkitLite\Api\Data\RedirectInterface;
use Amasty\SeoToolkitLite\Model\Redirect\RedirectGetter;
use Amasty\SeoToolkitLite\Model\Redirect\TargetPathResolver;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\UrlInterface;

class RedirectRouterAbstract implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var RedirectGetter
     */
    private $redirectGetter;

    /**
     * @var TargetPathResolver
     */
    private $targetPathResolver;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        RedirectGetter $redirectGetter,
        TargetPathResolver $targetPathResolver,
        UrlInterface $url
    ) {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->redirectGetter = $redirectGetter;
        $this->targetPathResolver = $targetPathResolver;
        $this->url = $url;
    }

    /**
     * @param RequestInterface $request
     * @return bool|\Magento\Framework\App\ActionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(RequestInterface $request)
    {
        $path = $request->getRequestUri();
        $redirect = $this->redirectGetter->getRedirect($path);

        if ($redirect && $this->isRedirectAllow($redirect)) {
            $targetPath = $this->targetPathResolver->getTargetPath($redirect, $path);

            if (!$redirect->getIsTargetPathExternal()) {
                $targetPath = $this->url->getUrl('', ['_direct' => $targetPath]);
            }

            $this->response->setRedirect(
                $targetPath,
                $redirect->getRedirectType()
            );
            $request->setDispatched(true);

            return $this->actionFactory->create(\Magento\Framework\App\Action\Redirect::class);
        }

        return false;
    }

    /**
     * @param RedirectInterface $redirect
     * @return bool
     */
    protected function isRedirectAllow(RedirectInterface $redirect): bool
    {
        return true;
    }
}

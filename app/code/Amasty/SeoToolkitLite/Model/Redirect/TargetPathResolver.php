<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\Redirect;

use Amasty\SeoToolkitLite\Api\Data\RedirectInterface;

class TargetPathResolver
{
    public function getTargetPath(RedirectInterface $redirect, string $path): string
    {
        $targetPath = $redirect->getTargetPath();

        if (strpos($redirect->getTargetPath(), '*') !== false) {
            $targetPath = trim($targetPath, '/');
            $requestPath = trim($redirect->getRequestPath(), '/');
            $targetPath = $this->getTargetPathWithAsterisk($targetPath, $requestPath, $path);
        }

        return ltrim($targetPath, '/');
    }

    private function getTargetPathWithAsterisk(string $targetPath, string $requestPath, string $path): string
    {
        $endPosition = strlen($targetPath) - 1;
        $isAsteriskInStartOrEnd = strpos($targetPath, '*') === 0 || strpos($targetPath, '*') === $endPosition;
        if ($isAsteriskInStartOrEnd) {
            $requestPath = str_replace('*', '', $requestPath);
            $targetPath = str_replace('*', '', $targetPath);
            $targetPath = str_replace($requestPath, $targetPath, $path);
        } else {
            $targetPath = $this->getTargetPathWithMiddleAsterisk($targetPath, $requestPath, $path);
        }

        return $targetPath;
    }

    private function getTargetPathWithMiddleAsterisk(string $targetPath, string $requestPath, string $path): string
    {
        $positionAsteriskTarget = strpos($targetPath, '*');
        $startSubstrTarget = substr($targetPath, 0, $positionAsteriskTarget);
        $endSubstrTarget = substr($targetPath, $positionAsteriskTarget + 1, strlen($targetPath));
        $positionAsteriskRequest = strpos($requestPath, '*');
        $startSubstrRequest = substr($requestPath, 0, $positionAsteriskRequest);
        $endSubstrRequest = substr($requestPath, $positionAsteriskRequest + 1, strlen($requestPath));
        $path = str_replace($startSubstrRequest, $startSubstrTarget, $path);
        $path = str_replace($endSubstrRequest, $endSubstrTarget, $path);

        return $path;
    }
}

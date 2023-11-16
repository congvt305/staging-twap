<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Test\Unit\Model\Redirect;

use Amasty\SeoToolkitLite\Model\Redirect;
use Amasty\SeoToolkitLite\Test\Unit\Traits;
use Amasty\SeoToolkitLite\Model\Redirect\TargetPathResolver;

/**
 * Class TargetPathResolverTest
 *
 * @see TargetPathResolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class TargetPathResolverTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers TargetPathResolver::getTargetPath
     * @dataProvider getTargetPathDataProvider
     */
    public function testGetTargetPath(string $requestPath, string $targetPath, string $path, string $result)
    {
        $model = $this->getObjectManager()->getObject(TargetPathResolver::class);
        $redirect = $this->getObjectManager()->getObject(Redirect::class);

        $redirect->setRequestPath($requestPath);
        $redirect->setTargetPath($targetPath);

        $this->assertEquals($result, $model->getTargetPath($redirect, $path));
    }

    /**
     * Data provider for getTargetPath test
     * @return array
     */
    public function getTargetPathDataProvider()
    {
        return [
            ['/aaa/', '/bbb/', '/aaa/', 'bbb/'],
            ['aaa/*', '/bbb/', '/aaa/', 'bbb/'],
            ['/aaa/*', '/ccc/*', '/aaa/vvv/', 'ccc/vvv/'],
            ['*/aaa/', '*/ddd/', '/bbb/aaa/', 'bbb/ddd/'],
            ['/aaa/*/bbb/', '/ccc/*/ddd/', '/aaa/nnn/bbb/', 'ccc/nnn/ddd/'],
        ];
    }
}

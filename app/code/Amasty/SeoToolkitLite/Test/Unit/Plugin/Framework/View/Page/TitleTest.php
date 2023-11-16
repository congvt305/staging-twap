<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Test\Unit\Plugin\Framework\View\Page;

use Amasty\SeoToolkitLite\Plugin\Framework\View\Page\Title;
use Amasty\SeoToolkitLite\Test\Unit\Traits;
use Magento\Catalog\Model\Product\ProductList\Toolbar;

/**
 * Class TitleTest
 *
 * @see Title
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class TitleTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @param string $title
     * @param bool $isEnabled
     * @param int $page
     * @param int|string $allProducts
     * @param string $result
     * @covers Title::afterGet
     * @dataProvider afterGetDataProvider
     */
    public function testAfterGet(string $title, bool $isEnabled, int $page, string $result)
    {
        $nativeTitle = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $config = $this->createMock(\Amasty\SeoToolkitLite\Helper\Config::class);
        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $plugin = $this->getObjectManager()->getObject(
            Title::class,
            [
                'config' => $config,
                'request' => $request,
            ]
        );

        $config->expects($this->any())->method('isAddPageToMetaTitleEnabled')->willReturn($isEnabled);
        if ($isEnabled) {
            $request->expects($this->at(0))->method('getParam')->with('p', false)->willReturn($page);
        }

        $this->assertEquals($result, $plugin->afterGet($nativeTitle, $title));
    }

    /**
     * Data provider for afterGet test
     * @return array
     */
    public function afterGetDataProvider()
    {
        return [
            ['text', false, 0, 'text'],
            ['text', true, 0, 'text'],
            ['text', true, 2, 'text | Page 2']
        ];
    }
}

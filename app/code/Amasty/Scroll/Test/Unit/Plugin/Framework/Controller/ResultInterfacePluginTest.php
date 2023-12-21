<?php

namespace Amasty\Scroll\Test\Unit\Plugin\Framework\Controller;

use Amasty\Scroll\Plugin\Framework\Controller\ResultInterfacePlugin;
use Amasty\Scroll\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Scroll\Test\Unit\Traits\ReflectionTrait;
use Magento\Theme\Block\Html\Pager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ResultInterfacePluginTest
 *
 * @see ResultInterfacePlugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ResultInterfacePluginTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers ResultInterfacePlugin::modifyBody
     *
     * @dataProvider modifyBodyDataProvider
     *
     * @throws \ReflectionException
     */
    public function testModifyBody($output, $linkContent, $expected)
    {
        /** @var MockObject|ResultInterfacePlugin $plugin */
        $plugin = $this->createPartialMock(ResultInterfacePlugin::class, ['getPrevNextLinkContent']);

        $plugin->expects($this->any())->method('getPrevNextLinkContent')->willReturn($linkContent);

        $this->assertEquals($expected, $plugin->modifyBody($output));
    }

    /**
     * @covers ResultInterfacePlugin::getPrevNextLinkContent
     *
     * @dataProvider getPrevNextLinkContentDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetPrevNextLinkContent($lastPage, $currentPage, $pageVarName, $currentUrl, $expected)
    {
        /** @var MockObject|ResultInterfacePlugin $plugin */
        $plugin = $this->createPartialMock(ResultInterfacePlugin::class, [
            'getPagerBlock',
            'getCurrentUrl'
        ]);

        $pagerMock = $this->createMock(Pager::class);
        $pagerMock->expects($this->any())->method('getLastPageNum')->willReturn($lastPage);
        $pagerMock->expects($this->any())->method('getCurrentPage')->willReturn($currentPage);
        $pagerMock->expects($this->any())->method('getPageVarName')->willReturn($pageVarName);
        $plugin->expects($this->any())->method('getPagerBlock')->willReturn($pagerMock);
        $plugin->expects($this->any())->method('getCurrentUrl')->willReturn($currentUrl);

        $this->assertEquals($expected, $plugin->getPrevNextLinkContent());
    }

    /**
     * Data provider for modifyBody test
     * @return array
     */
    public function getPrevNextLinkContentDataProvider()
    {
        return [
            [1, 1, 'abc', 'test.com', ''],
            [2, 1, 'abc', 'test.com', '<link rel="next" href="test.com?abc=2" />' . PHP_EOL],
            [2, 2, 'abc', 'test.com', '<link rel="prev" href="test.com" />' . PHP_EOL],
            [3, 2, 'abc', 'test.com', '<link rel="prev" href="test.com" />' . PHP_EOL . '<link rel="next" href="test.com?abc=3" />' . PHP_EOL],
            [2, 2, 'abc', 'test.com?p=2&amp;abc=1', '<link rel="prev" href="test.com?p=2" />' . PHP_EOL],
            [2, 2, 'abc', 'test.com?abc=1', '<link rel="prev" href="test.com" />' . PHP_EOL],
            [3, 2, 'abc', 'test.com?x=2', '<link rel="prev" href="test.com?x=2" />' . PHP_EOL . '<link rel="next" href="test.com?x=2&amp;abc=3" />' . PHP_EOL]
        ];
    }

    /**
     * Data provider for modifyBody test
     * @return array
     */
    public function modifyBodyDataProvider()
    {
        return [
            [
                'test</head>',
                '<a href="">test</a>',
                'test<a href="">test</a></head>'
            ],
            [
                'test',
                '<a href="">test</a>',
                'test'
            ],
            [
                'test</head>',
                '',
                'test</head>'
            ]
        ];
    }
}

<?php

declare(strict_types=1);

namespace Amasty\OpenGraphTags\Test\Unit\Model\Attribute;

use Amasty\OpenGraphTags\Model\Attribute\ProductProcessor;
use Amasty\OpenGraphTags\Model\ConfigProvider;
use Amasty\OpenGraphTags\Model\Meta\GetReplacedMetaData;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @see ProductProcessor
 */
class ProductProcessorTest extends TestCase
{
    /**
     * @covers ProductProcessor::getAttributeValue
     * @dataProvider getAttributeValueProvider
     *
     * @param string $attributeCode
     * @param string|null $replacedMetaData
     * @param bool $usesSource
     * @param string|array $attributeText
     * @param string $expectedResult
     * @return void
     */
    public function testGetAttributeValue(
        string $attributeCode,
        ?string $replacedMetaData,
        bool $usesSource,
        $attributeText,
        string $expectedResult
    ): void {
        $product = $this->createMock(Product::class);
        $productAttributeRepository = $this->createMock(ProductAttributeRepositoryInterface::class);
        $attribute = $this->createMock(AbstractAttribute::class);
        $configProvider = $this->createMock(ConfigProvider::class);
        $logger = $this->createMock(LoggerInterface::class);
        $getReplacedMetaData = $this->createMock(GetReplacedMetaData::class);

        $getReplacedMetaData->expects($this->any())
            ->method('execute')
            ->willReturnCallback(
                function () use ($replacedMetaData) {
                    return $replacedMetaData;
                }
            );

        $configProvider->expects($this->any())
            ->method('getProductPageTitleAttribute')
            ->willReturn($attributeCode);

        $productAttributeRepository->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function ($variable) use ($attribute, $attributeCode) {
                    if (!$attributeCode) {
                        throw new NoSuchEntityException(__('exception'));
                    } else {
                        return $attribute;
                    }
                }
            );

        $attribute->expects($this->any())
            ->method('usesSource')
            ->willReturn($usesSource);

        $product->expects($this->any())
            ->method('getAttributeText')
            ->willReturn($attributeText);

        $product->expects($this->any())
            ->method('getData')
            ->willReturnCallback(
                function ($variable) {
                    return $variable;
                }
            );

        $processor = new ProductProcessor(
            $productAttributeRepository,
            $configProvider,
            $logger,
            $getReplacedMetaData
        );

        $testMethod = new \ReflectionMethod(
            ProductProcessor::class,
            'getAttributeValue'
        );
        $testMethod->setAccessible(true);

        $this->assertEquals(
            $expectedResult,
            $testMethod->invoke($processor, $attributeCode, $product)
        );
    }

    /**
     * Data provider for getAttributeValue test
     * @return array
     */
    public function getAttributeValueProvider(): array
    {
        return [
            ['', null, true, '', ''],
            ['title', 'metadata', false, '', 'metadata'],
            ['color', null, true, 'test', 'test'],
            ['size', null, true, ['test1', 'test2'], 'test1,test2'],
            ['text', null, false, '', 'text']
        ];
    }
}

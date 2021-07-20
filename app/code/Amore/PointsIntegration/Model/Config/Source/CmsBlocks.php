<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 6/7/21
 * Time: 1:35 PM
 */
declare(strict_types=1);

namespace Amore\PointsIntegration\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * To provide cms block options in form
 *
 * Class CmsBlocks
 */
class CmsBlocks implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Return block options array
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $options = $this->collectionFactory->create()->toOptionArray();
        array_splice(
            $options,
            0,
            0,
            [
                ['value' => '', 'label' => __('Select Block')]
            ]
        );
        return $options;
    }
}

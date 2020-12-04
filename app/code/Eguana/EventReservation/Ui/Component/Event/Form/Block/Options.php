<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 20/10/20
 * Time: 3:21 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\Component\Event\Form\Block;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * To provide cms block options in form
 *
 * Class Options
 */
class Options implements OptionSourceInterface
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
     * Return option array
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

<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 30/11/20
 * Time: 5:45 PM
 */
namespace Eguana\LinePay\Model\LinePay\Checkout;

use Magento\Framework\ObjectManagerInterface;
use Eguana\LinePay\Model\LinePay\Checkout;

/**
 * Factory class for \Eguana\LinePay\Model\LinePay\Checkout
 */
class Factory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $className
     * @param array $data
     * @return Checkout
     */
    public function create($className, array $data = [])
    {
        return $this->_objectManager->create($className, $data);
    }
}

<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 22/12/20
 * Time: 11:35 AM
 */
namespace Eguana\SocialLogin\Setup\Patch\Data;

use Eguana\SocialLogin\Model\ResourceModel\SocialLogin\CollectionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class TruncateLineData
 *
 * Truncate eguana_sociallogin_customer table
 */
class TruncateLineData implements DataPatchInterface
{

    /**
     * @var CollectionFactory
     */
    private $model;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TruncateLineData constructor.
     * @param CollectionFactory $model
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $model,
        LoggerInterface $logger
    ) {
        $this->model  = $model;
        $this->logger = $logger;
    }

    /**
     * Get dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Truncate eguana_sociallogin_customer table
     * @return TruncateLineData|void
     */
    public function apply()
    {
        try {
            $modelObj = $this->model->create();
            $items = $modelObj->getItems();
            foreach ($items as $item) {
                $item->delete();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}

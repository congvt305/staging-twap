<?php
declare(strict_types=1);

namespace CJ\Catalog\Helper;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \CJ\Catalog\Model\Config
     */
    protected $config;

    /**
     * @inheritDoc
     */
    public function __construct(
        \CJ\Catalog\Model\Config $config,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Get config model
     *
     * @return \CJ\Catalog\Model\Config
     */
    public function getConfigHelper(): \CJ\Catalog\Model\Config
    {
        return $this->config;
    }
}

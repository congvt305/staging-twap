<?php

namespace CJ\CustomCookie\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Setup\Exception;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Api\BlockRepositoryInterface;

class Data extends AbstractHelper
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        Context $context
    ) {
        $this->blockRepository = $blockRepository;
        parent::__construct($context);
    }

    /**
     * constant cms block id for cookie template
     */
    const COOKIE_TEMPLATE_CMS_BLOCK_ID = 'web/cookie/cookie_cms_block_id';

    /**
     * Get cookie template block id
     *
     * @return mixed
     */
    public function getCookieTemplateBlockId()
    {
        return $this->scopeConfig->getValue(
            self::COOKIE_TEMPLATE_CMS_BLOCK_ID,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get CMS Block Identifier
     *
     * @return string
     */
    public function getCmsBlockIdentifier()
    {
        $id = $this->getCookieTemplateBlockId();
        $identifier = '';
        if($id) {
            try {
                $block = $this->blockRepository->getById($id);
                $identifier = $block->getIdentifier();
            } catch (\Exception $exception) {
                throw new \Exception('Cookie Template block identifier error:' . $exception->getMessage());
            }
        }
        return $identifier;
    }
}

<?php
declare(strict_types=1);

namespace CJ\Catalog\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENABLED_REMOVE_SPECIAL_CHARACTER = 'catalog/remove_special_character/enabled';

    const LIST_SPECIAL_CHARACTER = 'catalog/remove_special_character/list';
    /**
     * @var \CJ\Catalog\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @inheritDoc
     */
    public function __construct(
        \CJ\Catalog\Model\Config $config,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->config = $config;
        $this->json = $json;
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

    /**
     * @param string $str
     * @return array
     */
    public function mb_chunk_split(string $str): array
    {
        $limit = (int)$this->config->getLimit();
        $result = [];
        $offset = 0;
        // VNMGDC-541 has a requirement that there are four lines
        for ($i = 0; $i < 4; $i++) {
            $offset += $limit;
            if ($new = mb_substr($str, $offset, $limit)) {
                $result[] = $new;
            } elseif (mb_strlen($str) <= $limit) {
                $result[] = $str;
                break;
            } else {
                break;
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isEnabledRemoveSpecialCharacter()
    {
        return $this->scopeConfig->isSetFlag(self::ENABLED_REMOVE_SPECIAL_CHARACTER, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|bool|float|int|mixed|string|null
     */
    public function getSpecialCharacterList()
    {
        $listSpecialCharacter = $this->scopeConfig->getValue(self::LIST_SPECIAL_CHARACTER, ScopeInterface::SCOPE_STORE);
        return $this->json->unserialize($listSpecialCharacter);
    }
}

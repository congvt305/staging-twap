<?php
namespace CJ\VLogicOrder\Model;

use Magento\Framework\Model\AbstractModel;
use CJ\VLogicOrder\Api\Data\TokenDataInterface;

class TokenData extends AbstractModel implements TokenDataInterface
{
    const TOKEN_ID = 'token_id';
    const TOKEN = 'token';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected function _construct() {
        $this->_init('CJ\VLogicOrder\Model\ResourceModel\TokenData');
    }

    /**
     * @return int
     */
    public function getTokenId(): int
    {
        return $this->getData(self::TOKEN_ID);
    }

    /**
     * @param int $tokenId
     * @return TokenDataInterface
     */
    public function setTokenId(int $tokenId): TokenDataInterface
    {
        return $this->setData(self::TOKEN_ID, $tokenId);
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * @param string $token
     * @return TokenDataInterface
     */
    public function setToken(string $token): TokenDataInterface
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param int $status
     * @return TokenDataInterface
     */
    public function setStatus(int $status): TokenDataInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return TokenDataInterface
     */
    public function setCreatedAt(string $createdAt): TokenDataInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return TokenDataInterface
     */
    public function setUpdatedAt(string $updatedAt): TokenDataInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

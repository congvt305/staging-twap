<?php
namespace CJ\NinjaVanShipping\Api\Data;

/**
 * Interface TokenDataInterface
 * @package CJ\NinjaVanShipping\Api\Data
 */
interface TokenDataInterface
{
    /**
     * @return int
     * @api
     */
    public function getTokenId(): int;

    /**
     * @param int $tokenId
     * @return TokenDataInterface
     */
    public function setTokenId(int $tokenId): TokenDataInterface;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return TokenDataInterface
     */
    public function setStatus(int $status): TokenDataInterface;

    /**
     * @return string|null
     */
    public function getToken();

    /**
     * @param string $token
     * @return TokenDataInterface
     */
    public function setToken(string $token): TokenDataInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): TokenDataInterface;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return TokenDataInterface
     */
    public function setUpdatedAt(string $updatedAt): TokenDataInterface;
}

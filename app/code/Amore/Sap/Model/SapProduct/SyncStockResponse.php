<?php
/**
 * Created by Eguana.
 * User: raheel
 * Date: 8/4/21
 * Time: 4:05 PM
 */
namespace Amore\Sap\Model\SapProduct;

/**
 * Response handler class for api to sync inventory stock
 *
 * Class SyncStockResponse
 */
class SyncStockResponse implements \Amore\Sap\Api\Data\SyncStockResponseInterface
{
    /**
     * @var
     */
    protected $code;

    /**
     * @var
     */
    protected $message;

    /**
     * @var
     */
    protected $data;

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return mixed|string
     */
    public function setCode($code)
    {
        return $this->code = $code;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return mixed|string
     */
    public function setMessage($message)
    {
        return $this->message = $message;
    }

    /**
     * Get data
     *
     * @return \Amore\Sap\Api\Data\SyncStockResponseStockDataInterface[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param \Amore\Sap\Api\Data\SyncStockResponseStockDataInterface[] $data
     * @return mixed
     */
    public function setData($data)
    {
        return $this->data = $data;
    }
}

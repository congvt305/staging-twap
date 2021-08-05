<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 11:24 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Client;


abstract class AbstractClient implements \Eguana\GWLogistics\Model\Gateway\Client\ClientInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Eguana\GWLogistics\Model\Lib\EcpayLogistics
     */
    protected $_ecpayLogistics;

    public function __construct(
        \Psr\Log\LoggerInterface $_logger,
        \Eguana\GWLogistics\Model\Lib\EcpayLogistics $_ecpayLogistics

    ) {
        $this->_logger = $_logger;
        $this->_ecpayLogistics = $_ecpayLogistics;
    }

    public function placeRequest(array $request): array
    {
        $response = [];

        try {
            $response = $this->process($request);
        } catch (\Exception $e) {
            $message = $e->getMessage() ? $e->getMessage() : "Something went wrong during Gateway request.";
            $log['error'] = $message;
            $this->_logger->debug('Some error occurred.', $log);
        }

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    abstract protected function process(array $data): array;
}

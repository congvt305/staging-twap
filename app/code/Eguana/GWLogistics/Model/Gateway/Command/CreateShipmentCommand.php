<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 9:31 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Command;

use Magento\Framework\Exception\LocalizedException;

class CreateShipmentCommand
{

    /**
     * @var \Eguana\GWLogistics\Model\Gateway\Request\RequestBuilderInterface
     */
    private $createShipmentRequestBuilder;
    /**
     * @var \Eguana\GWLogistics\Model\Gateway\Request\RequestBuilderInterface
     */
    private $queryLogisticsRequestBuilder;
    /**
     * @var \Eguana\GWLogistics\Model\Gateway\Client\ClientInterface
     */
    private $createShipmentClient;
    /**
     * @var \Eguana\GWLogistics\Model\Gateway\Client\ClientInterface
     */
    private $queryLogisticsInfoClient;

    /**
     * @var \Eguana\GWLogistics\Model\Gateway\Response\HandlerInterface
     */
    private $handler;
    /**
     * @var \Eguana\GWLogistics\Model\Gateway\Validator\ValidatorInterface
     */
    private $createShipmentValidator;
    /**
     * @var \Eguana\GWLogistics\Model\Gateway\Validator\ValidatorInterface
     */
    private $queryLogisticsInfoValidator;

    public function __construct(
        \Eguana\GWLogistics\Model\Gateway\Request\RequestBuilderInterface $createShipmentRequestBuilder,
        \Eguana\GWLogistics\Model\Gateway\Request\RequestBuilderInterface $queryLogisticsRequestBuilder,
        \Eguana\GWLogistics\Model\Gateway\Client\ClientInterface $createShipmentClient,
        \Eguana\GWLogistics\Model\Gateway\Client\ClientInterface $queryLogisticsInfoClient,
        \Eguana\GWLogistics\Model\Gateway\Validator\ValidatorInterface $createShipmentValidator,
        \Eguana\GWLogistics\Model\Gateway\Validator\ValidatorInterface $queryLogisticsInfoValidator,
        \Eguana\GWLogistics\Model\Gateway\Response\HandlerInterface $handler
    ) {

        $this->createShipmentRequestBuilder = $createShipmentRequestBuilder;
        $this->queryLogisticsRequestBuilder = $queryLogisticsRequestBuilder;
        $this->createShipmentClient = $createShipmentClient;
        $this->queryLogisticsInfoClient = $queryLogisticsInfoClient;
        $this->handler = $handler;
        $this->createShipmentValidator = $createShipmentValidator;
        $this->queryLogisticsInfoValidator = $queryLogisticsInfoValidator;
    }

    /**
     * @throws \Exception
     */
    public function execute($order)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $createShipmentRequest = $this->createShipmentRequestBuilder->build(['order' => $order]);
            $createShipmentResponse = $this->createShipmentClient->placeRequest($createShipmentRequest);
            // If any exception is throw in function placeRequest, it will return an empty array
            // so we cannot call function validate($createShipmentResponse)
            if (empty($createShipmentResponse)) {
                throw new LocalizedException(__('Something went wrong during Gateway request.'));
            }

            if ($this->createShipmentValidator !== null) {
                $createShipmentResult = $this->createShipmentValidator->validate($createShipmentResponse);
                if (!$createShipmentResult->isValid()) {
                    $this->processErrors($createShipmentResult);
                }
            }

            $queryLogisticsRequest = $this->queryLogisticsRequestBuilder->build([
                'order' => $order,
                'createShipmentResponse' => $createShipmentResponse
            ]);
            $queryLogisticsResponse = $this->queryLogisticsInfoClient->placeRequest($queryLogisticsRequest);

            if ($this->queryLogisticsInfoValidator !== null) {
                $createShipmentResult = $this->queryLogisticsInfoValidator->validate($queryLogisticsResponse);
                if (!$createShipmentResult->isValid()) {
                    $this->processErrors($createShipmentResult);
                }
            }

            $mergedResponse = [
                'createShipmentResponse' => $createShipmentResponse,
                'queryLogisticsResponse' => $queryLogisticsResponse
            ];

            $commandSubject = [
                'order' => $order
            ];

            if ($this->handler) {
                $this->handler->handle($commandSubject, $mergedResponse);
            }

        } catch (\Exception $e) {
//            $this->logger
            throw $e;
        }
    }

    private function processErrors($result)
    {
        return false;
    }

}

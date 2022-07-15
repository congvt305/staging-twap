<?php

namespace CJ\LineShopping\Model\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class RestClient
{
    const CONNECT_TIMEOUT = 10;

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @param Client $client
     */
    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    /**
     * @param $uri
     * @param array $headers
     * @param array $options
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($uri, array $headers = [], array $options = [])
    {
        $request = new Request('GET', $uri, $headers);
        $response = $this->sendRequest($request, $options);
        return $response;
    }

    /**
     * @param $uri
     * @param array $params
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($uri, array $params = [])
    {
        $request = new Request('POST', $uri);
        $options = [
            'form_params' => $params
        ];
        $response = $this->sendRequest($request, $options);
        return $response;
    }

    /**
     * @param Request $request
     * @param $options
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendRequest(Request $request, array $options = [])
    {
        $connectTimeout = ['connect_timeout' => self::CONNECT_TIMEOUT];
        $requestOptions = array_merge($options, $connectTimeout);
        return $this->client->send($request, $requestOptions);
    }

    /**
     * @param $uri
     * @param $channelAccessToken
     * @param $body
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendMessageClient($uri, $channelAccessToken, $body)
    {
        $header = [
            "Authorization" =>  "Bearer " . $channelAccessToken,
            "Content-Type" => "application/json"
        ];
        $request = new Request('POST', $uri, $header, $body);
        return $this->sendRequest($request);
    }
}

<?php

namespace CJ\Cms\Helper;

use GuzzleHttp\RequestOptions;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use GuzzleHttp\ClientFactory;
use Magento\Framework\DataObject;

/**
 * Class Request
 * @package CJ\Cms\Helper
 */
class Request extends Data
{
    /**
     * @var PsrLoggerInterface
     */
    private $logger;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * Request constructor.
     * @param ClientFactory $clientFactory
     * @param Json $json
     * @param PsrLoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        ClientFactory $clientFactory,
        Json $json,
        PsrLoggerInterface $logger,
        Context $context
    )
    {
        parent::__construct($context);
        $this->logger = $logger;
        $this->json = $json;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @return DataObject
     */
    public function getCmsData($searchPath)
    {
        $client = $this->clientFactory->create(['config' => $this->prepareHeaders()]);
        $uri = $this->getMigrateUrl() . $searchPath . $this->prepareParams();
        $this->logger->info('Request body to generate Migrate CMS:');
        $this->logger->info($this->json->serialize(['config' => $client->getConfig(), 'uri' => $uri]));
        $response = $client->get($uri);
        $contents = $this->json->unserialize($response->getBody()->getContents());

        return new DataObject($contents);
    }

    /**
     * @return array
     */
    protected function prepareHeaders()
    {
        return [
            RequestOptions::HEADERS => ['Authorization' => "Bearer {$this->getMigrateToken()}"],
            RequestOptions::VERIFY => false,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => 60
        ];
    }

    /**
     * @return string
     */
    protected function prepareParams(): string
    {
        return '?' . http_build_query([
                'searchCriteria[filter_groups][0][filters][0][field]' => 'store_id',
                'searchCriteria[filter_groups][0][filters][0][value]' => $this->getMigrateStoreId(),
                'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'eq'
            ]);
    }
}

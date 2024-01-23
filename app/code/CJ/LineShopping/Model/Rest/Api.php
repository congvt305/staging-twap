<?php

namespace CJ\LineShopping\Model\Rest;

use Exception;
use CJ\LineShopping\Helper\Config;
use CJ\LineShopping\Helper\Data;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Magento\Catalog\Api\ProductRepositoryInterface;
use CJ\LineShopping\Logger\Logger;

class Api
{
    const API_LINE_SHOPPING_URL = 'url';
    const API_LINE_SHOPPING_ID = 'site';
    const API_LINE_SHOPPING_SHOP_ID = 'shop_id';
    const API_LINE_SHOPPING_AUTH_KEY = 'auth_key';
    const ORDER_POST_BACK_PATH = '/tracking/orderinfo';
    const FEE_POST_BACK_PATH = '/tracking/orderfinish';
    const LINE_SHOPPING_SUCCESS_MESSAGE = 'OK';
    const PRODUCT_TYPE_NORMAL = 'normal';
    const TIME_ZONE_8 = 'Asia/Hong_Kong';

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var Data
     */
    protected Data $dataHelper;

    /**
     * @var RestClient
     */
    protected RestClient $restClient;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     * @param Config $config
     * @param Data $dataHelper
     * @param RestClient $restClient
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Logger $logger,
        Config $config,
        Data $dataHelper,
        RestClient $restClient,
        ProductRepositoryInterface $productRepositoryInterface,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->restClient = $restClient;
        $this->productRepository = $productRepositoryInterface;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param $websiteId
     * @return array
     */
    protected function getLineInfo($websiteId = null): array
    {
        $lineInfo['apiUrl'] = $this->config->getApiConfigValue(self::API_LINE_SHOPPING_URL, $websiteId);
        $lineInfo['site'] = $this->config->getApiConfigValue(self::API_LINE_SHOPPING_ID , $websiteId);
        $lineInfo['shopId'] = $this->config->getApiConfigValue(self::API_LINE_SHOPPING_SHOP_ID, $websiteId);
        $lineInfo['authKey'] = $this->config->getApiConfigValue(self::API_LINE_SHOPPING_AUTH_KEY, $websiteId, true);
        return $lineInfo;
    }

    /**
     * @param $order
     * @return array|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function orderPostBack($order)
    {
        try {
            $lineInfo = $this->getLineInfo($order->getStore()->getWebsiteId());
            $postData = $this->preparePostData($order, $lineInfo);
            $endpoint = $lineInfo['apiUrl'] . self::ORDER_POST_BACK_PATH;
            //request to LINE
            $response = $this->restClient->post($endpoint, $postData);

            $statusCode = $response->getStatusCode();
            if ($statusCode != Response::HTTP_OK) {
                throw new \LogicException('Response error with status code: ' . $statusCode);
            }

            $result = $response->getBody()->getContents();

            $this->logger->info(Logger::ORDER_POST_BACK,
                [
                    'request_data' => $postData,
                    'result' => $result
                ]);
            return $result;
        } catch (Exception $exception) {
            $this->logger->error(Logger::ORDER_POST_BACK,
                [
                    'error' => $exception->getMessage()
                ]);
        } catch (GuzzleException $exception) {
            $this->logger->error(Logger::ORDER_POST_BACK,
                [
                    'error' => $exception->getMessage()
                ]);
        }
        return [];
    }

    /**
     * @param $order
     * @return array|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function feePostBack($order)
    {
        try {
            $lineInfo = $this->getLineInfo($order->getStore()->getWebsiteId());
            $postData = $this->prepareFeeData($order, $lineInfo);
            $endpoint = $lineInfo['apiUrl'] . self::FEE_POST_BACK_PATH;

            //request
            $response = $this->restClient->post($endpoint, $postData);

            $statusCode = $response->getStatusCode();
            if ($statusCode != Response::HTTP_OK) {
                throw new \LogicException('Response error with status code: ' . $statusCode);
            }

            $result = $response->getBody()->getContents();

            $this->logger->info(Logger::FEE_POST_BACK,
                [
                    'request_data' => $postData,
                    'result' => $result
                ]);
            return $result;
        } catch (\Exception $exception) {
            $this->logger->error(Logger::FEE_POST_BACK,
                [
                    'error' => $exception->getMessage()
                ]);
        } catch (GuzzleException $exception) {
            $this->logger->error(Logger::FEE_POST_BACK,
                [
                    'error' => $exception->getMessage()
                ]);
        }

        return [];
    }

    /**
     * @param $order
     * @param $lineInfo
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function preparePostData($order, $lineInfo): array
    {
        $data = $this->getGeneralInfomation($order, $lineInfo);

        //order_list
        $orderList = $this->getOrderItemList($order);
        $data['order_list'] = json_encode($orderList, JSON_UNESCAPED_UNICODE);
        $data['ordertotal'] = 0;
        if(is_array($orderList) && count($orderList) > 0) {
            foreach ($orderList as $item) {
                $data['ordertotal'] += round($item['product']['product_amount']);
            }
        }
        $data['ordertime'] = $this->getOrderTime($order);
        $data['timestamp'] = time();

        //hash
        $hashHmacData = 'orderid=' . $order->getIncrementId() .
            '&ordertotal=' . $data['ordertotal'] .
            '&timestamp=' . $data['timestamp'];
        // @codingStandardsIgnoreStart
        $hashHmac = hash_hmac('sha256', $hashHmacData, md5($this->getOrderTime($order)));
        // @codingStandardsIgnoreEnd
        $data['hash'] = $hashHmac;

	    $data[Data::IS_NEW_MEMBER] = false;
		if ($this->dataHelper->isNewMember($order)){
			$data[Data::IS_NEW_MEMBER] = true;
		}
        return $data;
    }

    /**
     * @param $order
     * @param $lineInfo
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareFeeData($order, $lineInfo): array
    {
        $data = $this->getGeneralInfomation($order, $lineInfo);

        $feeList = $this->getFeeItemList($order);
        $data['fee_list'] = json_encode($feeList, JSON_UNESCAPED_UNICODE);
        $data['feetotal'] = 0;
        if(is_array($feeList) && count($feeList) > 0) {
            foreach ($feeList as $item) {
                $data['feetotal'] += round($item['product']['product_fee']);
            }
        }
        $data['feetime'] = $this->getFeeTime($order);
        $data['ordertime'] = $this->getOrderTime($order);

        //hash
        $data['timestamp'] = time();
        $hashHmacData = 'orderid=' . $order->getIncrementId() .
            '&feetime=' . $data['feetime'] .
            '&feetotal=' . $data['feetotal'] .
            '&timestamp=' . $data['timestamp'];
        // @codingStandardsIgnoreStart
        $hashHmac = hash_hmac('sha256', $hashHmacData, md5($data['feetime']));
        // @codingStandardsIgnoreEnd
        $data['hash'] = $hashHmac;
        return $data;
    }

    /**
     * @param $order
     * @param $lineInfo
     * @return array
     */
    protected function getGeneralInfomation($order, $lineInfo): array
    {
        $data['site'] = $lineInfo['site'];
        $data['shopid'] = $lineInfo['shopId'];
        $data['authkey'] = $lineInfo['authKey'];
        $data['orderid'] = $order->getIncrementId();
        $data['ecid'] = $order->getLineEcid();
        return $data;
    }

    /**
     * @param $order
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getOrderItemList($order): array
    {
        $rootCategoryId = $order->getStore()->getRootCategoryId();
        $visibleItems = $this->getAllVisibleItems($order);
        $orderList = [];
        /** @var  \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($visibleItems as $item) {
            $productId = $item->getProductId();
            $product = $this->productRepository->getById($productId);
            $subCatData = $this->getSubCatData($product, $rootCategoryId);

            //product_amount
            $rowTotalInclTax = $item->getRowTotalInclTax();
            $discountAmount = $this->getTotalDiscountAmount($order, $item);
            $productAmount = number_format(
                $rowTotalInclTax - $discountAmount,
                0,
                '.',
                ''
            );
            $orderList[] = [
                'product' => array_merge([
                    'product_name' => $item->getName(),
                    'product_type' => self::PRODUCT_TYPE_NORMAL,
                    'product_id' => $product->getSku(),
                    'product_amount' => $productAmount
                ], $subCatData)
            ];
        }
        return $orderList;
    }

    /**
     * @param $product
     * @param $rootCategoryId
     * @return string[]
     */
    protected function getSubCatData($product, $rootCategoryId): array
    {
        $result = [];
        try {
            /** @var \Magento\Framework\Data\Collection $catCollection */
            $catCollection = $product->getCategoryCollection();
            $pathArray = [];
            foreach ($catCollection as $cat) {
                $pathArray[] = $cat->getPath();
            }
            while (count($pathArray) > 0) {
                $lengths = array_map('strlen', $pathArray);
                $longestPath = $pathArray[array_search(max($lengths), $lengths)];
                $path = $longestPath ? $longestPath : '';
                if (!$path) {
                    return $result;
                }
                $catIds = explode('/', $path);
                //subCat 1
                $subCatId1 = end($catIds);
                //load cat1 name
                if ($subCatId1 != $rootCategoryId) {
                    $subCat1 = $this->categoryRepository->get($subCatId1);
                    if (!$subCat1->getIsActive()) {
                        if (($key = array_search($longestPath, $pathArray)) !== false) {
                            unset($pathArray[$key]);
                            continue;
                        }
                    }
                    $subCat1Name = $subCat1->getName();
                    $result['sub_category1'] = $subCat1Name;
                } else {
                    return $result;
                }
                array_pop($catIds);
                //subCat 2
                $subCatId2 = end($catIds);
                if ($subCatId2 != $rootCategoryId) {
                    //load cat2 name
                    $subCat2 = $this->categoryRepository->get($subCatId2);
                    if ($subCat1->getIsActive()) {
                        $subCat2Name = $subCat2->getName();
                        $result['sub_category2'] = $subCat2Name;
                    }
                }
                break;
            }
        } catch (\Exception $e) {
            return $result;
        }
        return $result;
    }

    /**
     * @param $order
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getFeeItemList($order): array
    {
        $rootCategoryId = $order->getStore()->getRootCategoryId();
        $visibleItems = $this->getAllVisibleItems($order);
        $orderList = [];
        /** @var  \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($visibleItems as $item) {
            $productId = $item->getProductId();
            $product = $this->productRepository->getById($productId);
            $subCatData = $this->getSubCatData($product, $rootCategoryId);

            //product_fee
            $rowTotalInclTax = $item->getRowTotalInclTax();
            $discountAmount = $this->getTotalDiscountAmount($order, $item);
            $rowTotal = $rowTotalInclTax - $discountAmount;

            $discountRefund = $item->getDiscountRefunded() ? $item->getDiscountRefunded() : 0;
            $rowRefunded = $item->getPriceInclTax() * $item->getQtyRefunded();
            $rowTotalRefunded = $rowRefunded  - $discountRefund;

            $productFee = number_format(
                $rowTotal - $rowTotalRefunded,
                0,
                '.',
                ''
            );
            $orderList[] = [
                'product' => array_merge([
                    'product_name' => $item->getName(),
                    'product_type' => self::PRODUCT_TYPE_NORMAL,
                    'product_id' => $product->getSku(),
                    'product_fee' => $productFee
                ], $subCatData)
            ];
        }
        return $orderList;
    }

    /**
     * @param $order
     * @return array
     */
    protected function getAllVisibleItems($order): array
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            if (!$item->isDeleted() && !$item->getParentItem()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @param $order
     * @param $parentItem
     * @return mixed
     */
    protected function getTotalDiscountAmount($order, $parentItem)
    {
        $discountAmount = $parentItem->getDiscountAmount();
        foreach ($order->getItems() as $item) {
            if (!$item->isDeleted() && $item->getParentItemId() == $parentItem->getId()) {
                $discountAmount += $item->getDiscountAmount();
            }
        }
        return $discountAmount;
    }

    /**
     * @param $order
     * @return string
     * @throws Exception
     */
    protected function getOrderTime($order)
    {
        return $this->dataHelper->convertTimeZone(
            $order->getCreatedAt(),
            self::TIME_ZONE_8
        );
    }

    /**
     * @param $order
     * @return string
     * @throws Exception
     */
    protected function getFeeTime($order)
    {
        $trialPeriod = $this->config->getTrialPeriod();
        $feeTime = new \DateTime($order->getCreatedAt() . ' +' . $trialPeriod . ' day');
        return $this->dataHelper->convertTimeZone(
            $feeTime,
            self::TIME_ZONE_8
        );
    }

    /**
     * @param $lineId
     * @param $resetPasswordUrl
     * @param $websiteId
     * @return array|string
     */
    public function sendMessageToLine($lineId, $resetPasswordUrl, $websiteId = null) {
        try {
            $endpointUrl = $this->config->getEndpointUrl($websiteId);
            $secretKey = $this->config->getSecretKey($websiteId);
            $welcomeText = $this->config->getWelcomeText($websiteId);
            if (!$endpointUrl || !$secretKey) {
                $body = $lineId . ' ' . $resetPasswordUrl;
                throw new Exception('Missing configuration Endpoint Url or Secret Key');
            }
            $data = [
                'bURI' => Config::XML_PATH_LINE_CUSTOMER_URL_MULTICAST,
                'bBody' => [
                    'to' => [$lineId],
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $welcomeText . ' ' . $resetPasswordUrl
                        ]
                    ]
                ]
            ];
            $body = json_encode($data);
            $secretKeyHash = base64_encode(hash_hmac('sha256', $body, $secretKey, true));
            $response = $this->restClient->sendMessageClient($endpointUrl, $secretKeyHash, $body);
            $statusCode = $response->getStatusCode();
            if ($statusCode != Response::HTTP_OK) {
                throw new \LogicException('Response error with status code: ' . $statusCode);
            }

            $result = $response->getBody()->getContents();

            $this->logger->info(Logger::LINE_CUSTOMER,
                [
                    'request_data' => $body,
                    'result' => $result
                ]);
            return $result;
        } catch (\Exception $exception) {
            $this->logger->error(Logger::LINE_CUSTOMER,
                [
                    'request_data' => $body,
                    'error' => $exception->getMessage()
                ]);
        } catch (GuzzleException $exception) {
            $this->logger->error(Logger::LINE_CUSTOMER, [
                'request_data' => $body,
                'error' => $exception->getMessage()
            ]);
        }
        return [];
    }
}

<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 14/12/20
 * Time: 8:20 PM
 */
namespace Eguana\Faq\Ui\Component\Listing\Column;

use Eguana\Faq\Model\FaqConfiguration\FaqConfiguration;
use Eguana\Faq\Api\FaqRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Ui\Component\Listing\Column\Store\Options;
use Psr\Log\LoggerInterface;

/**
 * To show the available categories in form
 *
 * Class FaqCategories
 */
class FaqCategories extends Options
{
    /**
     * @var FaqConfiguration
     */
    private $faqConfiguration;

    /**
     * @var FaqRepositoryInterface
     */
    private $faqRepository;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Json $json
     * @param FaqRepositoryInterface $faqRepository
     * @param RequestInterface $request
     * @param FaqConfiguration $faqConfiguration
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $json,
        FaqRepositoryInterface $faqRepository,
        RequestInterface $request,
        FaqConfiguration $faqConfiguration,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->faqConfiguration = $faqConfiguration;
        $this->json = $json;
        $this->logger = $logger;
        $this->faqRepository = $faqRepository;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * Get categories options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeCategoryList = [];
        try {
            $isStoreIdZero = false;
            $id = $this->request->getParam('entity_id');
            if (isset($id)) {
                $faqs = $this->faqRepository->getById($this->request->getParam('entity_id'));
                if ($faqs['store_id'][0] == 0) {
                    $isStoreIdZero = true;
                }
            }
            $categoryList = [];
            $storeId[] = [];
            $param = 0;
            if (!isset($faqs) || $isStoreIdZero) {
                $storeManagerDataList = $this->storeManager->getStores();
                foreach ($storeManagerDataList as $key => $value) {
                    $storeId[] = $key;
                }
            } else {
                $storeId = $faqs['store_id'];
                $param = 1;
            }
            $i = 0;
            $index = 0;
            foreach ($storeId as $key) {
                if ($i == 0 && $param == 0) {
                    $i++;
                    continue;
                } else {
                    $id = $key;
                }
                $result = $this->faqConfiguration->getCategory($id);
                $categoryId = 0;
                if (count($result) > 0) {
                    foreach ($result as $category) {
                        $categoryList[$index][] = [
                            'label' => $category,
                            'value' => $id . '.' . $categoryId
                        ];
                        $categoryId++;
                    }
                    $storeCategoryList[$index] = [
                        'label'   => $this->storeManager->getStore($id)->getName(),
                        'value'   => $categoryList[$index]
                    ];
                    $index++;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $storeCategoryList;
    }
}

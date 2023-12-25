<?php


namespace Sapt\Newsletter\Plugin;


use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Newsletter\Controller\Subscriber\NewAction;
use Magento\Store\Model\StoreManagerInterface;

class SubscriberNewAction
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        JsonFactory $jsonFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param NewAction $subject
     * @param $result
     * @return Json
     */
    public function afterExecute(
        NewAction $subject,
        $result
    ) {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE && $subject->getRequest()->isXmlHttpRequest()) {
            $resultJson = $this->jsonFactory->create();
            return $resultJson->setData(['success' => true]);
        }
        return $result;
    }
}

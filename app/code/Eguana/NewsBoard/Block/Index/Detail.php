<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 22/10/20
 * Time: 4:00 PM
 */
namespace Eguana\NewsBoard\Block\Index;

use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Eguana\NewsBoard\Model\News;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Psr\Log\LoggerInterface;

/**
 * class NewsDetails
 *
 * block for details.phtml
 */
class Detail extends Template implements IdentityInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var NewsRepositoryInterface
     */
    private $newsRepository;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * Nesw constructor.
     *
     * @param Context $context
     * @param NewsRepositoryInterface $newsRepository
     * @param RequestInterface $requestInterface
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        NewsRepositoryInterface $newsRepository,
        RequestInterface $requestInterface,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->newsRepository = $newsRepository;
        $this->requestInterface = $requestInterface;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [News::CACHE_TAG];
    }

    /**
     * get News Method
     *
     * @return News
     */
    public function getNews()
    {
        /** @var News $news*/
        $id = $this->requestInterface->getParam('news_id');
        $news = "";
        try {
            $news = $this->newsRepository->getById($id);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $news;
    }

    /**
     * To set metatitle metakeyword and description
     * @return $this|Detail
     */

    protected function _prepareLayout()
    {

        parent::_prepareLayout();
        if ($this->getRequest()->getParam('news_id')) {
            $newsData = $this->getNews();
            $metaTitle = $newsData->getMetaTitle();
            $title = $metaTitle ? $metaTitle : $newsData->getTitle();
            $this->pageConfig->getTitle()->set(__($title));
            if (!empty($newsData->getData())) {
                $this->pageConfig->setMetaTitle(__($metaTitle));
                $this->pageConfig->setKeywords(__($newsData->getMetaKeywords()));
                $this->pageConfig->setDescription(__($newsData->getMetaDescription()));
            }
        }
        return $this;
    }
}

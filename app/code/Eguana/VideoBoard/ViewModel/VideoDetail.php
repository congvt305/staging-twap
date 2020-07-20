<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 23/6/20
 * Time: 10:01 PM
 */
namespace Eguana\VideoBoard\ViewModel;

use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;

/**
 * This ViewModel is used to show single video detail
 *
 * Class VideoDetail
 */
class VideoDetail implements ArgumentInterface
{
    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var VideoBoardRepositoryInterface
     */
    private $videoBoardRepository;

    /**
     * @var Http
     */
    private $request;

    /**
     * VideoBoard constructor.
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     * @param VideoBoardRepositoryInterface $videoBoardRepository
     * @param array $data
     */
    public function __construct(
        Http $request,
        FilterProvider $filterProvider,
        VideoBoardRepositoryInterface $videoBoardRepository,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->request = $request;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
        $this->videoBoardRepository = $videoBoardRepository;
    }

    /**
     * Get video id
     *
     * @return mixed
     */
    private function getVideoBoardId()
    {
        return $this->request->getParam('id');
    }

    /**
     * get video board method
     *
     * @return VideoBoard
     */
    public function getVideoBoard()
    {
        /** @var VideoBoard $videoBoard */
        $videoBoard = $this->videoBoardRepository->getById($this->getVideoBoardId());
        return $videoBoard;
    }

    /**
     * To filter the content
     * This function will get the content, specially the page builder content and make it renderable at frontend.
     * @param $content
     * @return mixed
     */
    public function contentFiltering($content)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($content);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [VideoBoard::CACHE_TAG];
    }

    /**
     * get store id
     * @return int
     */
    public function getStoreId()
    {
        try {
            return $this->_storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->_logger->debug($exception->getMessage());
        }
    }
}

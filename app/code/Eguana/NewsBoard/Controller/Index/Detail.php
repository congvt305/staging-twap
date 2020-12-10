<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 4:46 PM
 */
declare(strict_types=1);

namespace Eguana\NewsBoard\Controller\Index;

use Eguana\NewsBoard\Api\Data\NewsInterface;
use Magento\Framework\App\Action\Action;
use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\RequestInterface;
use Eguana\NewsBoard\Model\NewsConfiguration\NewsConfiguration;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * This controller will display black page
 *
 * Class Deatil
 */
class Detail extends Action
{

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var NewsRepositoryInterface
     */
    private $newsRepository;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var NewsConfiguration
     */
    private $newsConfiguration;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * Detail constructor.
     * @param Context $context
     * @param NewsRepositoryInterface $newsRepository
     * @param NewsConfiguration $newsConfiguration
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param PageFactory $pageFactory
     * @param DateTime $date
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        NewsRepositoryInterface $newsRepository,
        NewsConfiguration $newsConfiguration,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        PageFactory $pageFactory,
        DateTime $date,
        LoggerInterface $logger
    ) {
        $this->pageFactory = $pageFactory;
        $this->newsRepository = $newsRepository;
        $this->request = $request;
        $this->date = $date;
        $this->redirectFactory = $redirectFactory;
        $this->logger = $logger;
        $this->newsConfiguration = $newsConfiguration;
        parent::__construct($context);
    }

    /**
     * To create blank page, execute method will be called
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */

    public function execute()
    {
        if ($this->getEnableValue() == 0) {
            return $this->redirectFactory->create()->setPath('/');
        }
        $newsId = (int)$this->request->getParam('news_id');
        if (!$newsId) {
            return $this->redirectFactory->create()->setPath('news/');
        } else {
            $active = $this->getNewsIsActive();
            if ($active == 0) {
                return $this->redirectFactory->create()->setPath('news/');
            }
        }
        return $this->pageFactory->create();
    }

    /**
     * Get Enable value of module from configuration
     *
     * @return mixed
     */
    public function getEnableValue()
    {
        return $this->newsConfiguration->getConfigValue('enabled');
    }
    /**
     * get Enable value of news using repository
     *
     * @return NewsInterface|string
     */
    public function getNewsIsActive()
    {
        $isActive = 0;
        try {
            $news = $this->newsRepository->getById($this->request->getParam('news_id'));
            $isActive = $news['is_active'];
            if ($isActive == 1) {
                if ($news['date'] < $this->date->gmtDate() || $news['date'] == $this->date->gmtDate()) {
                    $isActive = 1;
                } else {
                    $isActive = 0;
                }
            }
            return $isActive;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $isActive;
    }
}

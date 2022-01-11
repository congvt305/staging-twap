<?php

namespace CJ\LineShopping\Controller;

use Exception;
use CJ\LineShopping\Model\FileSystem\FeedOutput;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultFactory;

abstract class MasterDownload implements HttpGetActionInterface
{
    /**
     * @var RawFactory
     */
    protected RawFactory $rawResultFactory;

    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var FeedOutput
     */
    protected FeedOutput $feedOutput;

    /**
     * @var ResultFactory
     */
    protected ResultFactory $resultFactory;

    /**
     * @param RawFactory $rawResultFactory
     * @param FeedOutput $feedOutput
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        RawFactory $rawResultFactory,
        FeedOutput $feedOutput,
        ResultFactory $resultFactory
    ) {
        $this->rawResultFactory = $rawResultFactory;
        $this->feedOutput = $feedOutput;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return Forward|\Magento\Framework\Controller\Result\Raw
     */
    public function getResponse()
    {
        try {
            $rawResult = $this->rawResultFactory->create();
            $output = $this->feedOutput->get($this->type);
            if (!$output) {
                /** @var Forward $resultForward */
                $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
                $resultForward->forward('noroute');
                return $resultForward;
            }
            $filename = $output['filename'];

            $rawResult->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/octet-stream', true)
                ->setHeader('Content-Length', strlen($output['content']), true)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"', true)
                ->setHeader('Last-Modified', date('r', $output['mtime']), true)
                ->setContents($output['content']);

            return $rawResult;
        } catch (Exception $exception) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
}

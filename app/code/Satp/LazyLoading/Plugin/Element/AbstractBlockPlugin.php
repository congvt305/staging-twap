<?php

namespace Satp\LazyLoading\Plugin\Element;

use Satp\LazyLoading\Model\Config;
use Magento\Framework\View\Element\AbstractBlock;
use Psr\Log\LoggerInterface;

class AbstractBlockPlugin
{
    /**
     * AbstractBlockPlugin constructor
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(
        LoggerInterface $logger,
        Config $config
    ) {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param AbstractBlock $block
     * @param string $html
     * @return string
     */
    public function afterToHtml(\Magento\Framework\View\Element\AbstractBlock $block, $html)
    {
        if (!$this->config->isLazyLoadingEnabled() || empty($html)) {
            return $html;
        }

        $document = $this->getDomDocumentFromHtml($html);
        $changes = $this->updateImageData($document);

        if ($document && $changes > 0) {
            $html = $document->saveHTML();
        }

        return $html;
    }

    /**
     * Inserts classes and attributes to enable lazy loading
     * @param \DOMDocument $document
     * @return int
     */
    private function updateImageData(\DOMDocument $document): int
    {
        $elements = $document->getElementsByTagName('img');
        $changes = 0;
        foreach($elements as $element) {
            if (!$element->getAttribute('loading')) {
                $element->setAttribute('loading', 'lazy');
                $changes++;
            }
        }
        return $changes;
    }

    /**
     * Create a DOMDocument from a string
     *
     * @param string $html
     *
     * @return \DOMDocument
     */
    private function getDomDocumentFromHtml(string $html) : \DOMDocument
    {
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        set_error_handler(
            function ($errorNumber, $errorString) {
                throw new \DOMException($errorString, $errorNumber);
            }
        );
        $string = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        try {
            libxml_use_internal_errors(true);
            $domDocument->loadHTML(
                '<html><body>' . $string . '</body></html>'
            );
            libxml_clear_errors();
        } catch (\Exception $e) {
            restore_error_handler();
            $this->logger->critical($e);
        }
        restore_error_handler();

        return $domDocument;
    }
}

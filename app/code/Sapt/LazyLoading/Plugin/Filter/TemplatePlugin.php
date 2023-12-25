<?php
namespace Sapt\LazyLoading\Plugin\Filter;

use Sapt\LazyLoading\Model\Config;
use Magento\Framework\Filter\Template;
use Psr\Log\LoggerInterface;

/**
 * Class TemplatePlugin
 * @package Sapt\LazyLoading\Plugin\Filter
 */
class TemplatePlugin
{
    const IMAGE_PATTERN = '/\<img/si';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \DOMDocument
     */
    private $domDocument;

    /**
     * TemplatePlugin constructor.
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
     * @param Template $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterFilter(Template $subject, string $result): string
    {
        if (!$this->config->isLazyLoadingEnabled()) {
            return $result;
        }

        $this->domDocument = false;

        if (preg_match(self::IMAGE_PATTERN, $result)) {
            $document = $this->getDomDocument($result);
            $changes = $this->updateImageData($document);
        }

        // If a document was retrieved we've modified the output so need to retrieve it from within the document
        if (isset($document) && isset($changes) && $changes > 0) {
            // Match the contents of the body from our generated document
            preg_match(
                '/<body>(.+)<\/body><\/html>$/si',
                $document->saveHTML(),
                $matches
            );

            if (!empty($matches)) {
                $result = $matches[1];
            }
        }

        return $result;
    }

    /**
     * Create a DOM document from a given string
     *
     * @param string $html
     *
     * @return \DOMDocument
     */
    private function getDomDocument(string $html) : \DOMDocument
    {
        if (!$this->domDocument) {
            $this->domDocument = $this->createDomDocument($html);
        }

        return $this->domDocument;
    }

    /**
     * Create a DOMDocument from a string
     *
     * @param string $html
     *
     * @return \DOMDocument
     */
    private function createDomDocument(string $html) : \DOMDocument
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

    /**
     * @param \DOMDocument $document
     * @return int
     */
    private function updateImageData(\DOMDocument $document): int
    {
        $changes = 0;
        $xpath = new \DOMXPath($document);
        // Get all pagebuilder images
        $nodes = $xpath->query('//*[@data-content-type]//descendant::img[@src]');

        /** @var \DOMElement $node */
        foreach ($nodes as $node) {
            $imageSrc = $node->getAttribute('src');

            if ($imageSrc !== '') {
                if (!$node->getAttribute('loading')) {
                    $node->setAttribute('loading', 'lazy');
                }
                $changes++;
            }
        }

        return $changes;
    }
}

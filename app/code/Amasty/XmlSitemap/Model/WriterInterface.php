<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model;

use Generator;

interface WriterInterface
{
    const PART_HEADER = 'header';
    const PART_FOOTER = 'footer';

    /**
     * @param Generator $data
     * @param array $parts
     */
    public function write(Generator $data, array $parts): void;

    /**
     * @param string $filePath
     */
    public function open(string $filePath): void;
}

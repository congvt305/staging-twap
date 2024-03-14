<?php

namespace CJ\Cms\Console;

/**
 * Class MigrateCmsBlockData
 * @package CJ\Cms\Console
 */
class MigrateCmsBlockData extends AbstractMigrateData
{
    const NAME = 'cj:migrate:cms_block';
    const REST_API_SEARCH_PATH = 'rest/V1/cmsBlock/search';

    /**
     * @return string
     */
    protected function getNameConsole(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    protected function getSearchPath(): string
    {
        return self::REST_API_SEARCH_PATH;
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return self::TYPE_BLOCK;
    }
}

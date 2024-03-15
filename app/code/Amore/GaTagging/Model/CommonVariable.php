<?php
declare(strict_types=1);

namespace Amore\GaTagging\Model;

/**
 * Class CommonVariable
 */
class CommonVariable
{
    /**
     * List of AP_DATA_ENV values
     */
    const ENV_LOCAL = 'LOCAL';
    const ENV_DEV = 'DEV';
    const ENV_STG = 'STG';
    const ENV_PRD = 'PRD';

    /**
     * Value of item_list_name on search page
     */
    const SEARCH_ITEM_LIST_NAME = 'SEARCH_RESULT';

    /**
     * Yes/No option values
     */
    const VALUE_YES = 'Y';
    const VALUE_NO = 'N';

    /**
     * Default AP values
     */
    const DEFAULT_DATA_CHANNEL = 'PC';
    const DEFAULT_PAGE_TYPE = 'OTHERS';
}

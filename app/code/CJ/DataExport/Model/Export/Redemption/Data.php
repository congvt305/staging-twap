<?php

namespace CJ\DataExport\Model\Export\Redemption;

/**
 * Class Data
 */
class Data
{
    /**
     * @var string[]
     */
    public static $excludeColumns = [
        'description',
        'store_name',
        'sms_content',
        'is_active',
        'image',
        'meta_description',
        'meta_keywords',
        'meta_title',
        'identifier',
        'thank_you_image',
        'end_date',
        'cms_block',
        'total_qty',
        'precautions',
        'vvip_list',
        'text_banner_index',
        'text_banner_hyperlink',
        'text_banner_success',
        'text_banner_success_hyperlink',
        'bg_color_text_banner',
        'redemption_completion_block',
        'redemption_completion_message',
        'text_banner_index_hyperlink'
    ];

    /**
     * @var string[]
     */
    public static $columns = [
        'entity_id' => 'id',
        'email' => 'email',
        'telephone' => 'telephone',
        'status' => 'status',
        'utm_source' => 'utm_source',
        'utm_medium' => 'utm_medium',
        'utm_content' => 'utm_content',
        'creation_time' => 'redeem_date',
        'update_time' => 'update_time'
    ];
}

<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/19/20
 * Time: 7:26 AM
 */

namespace Eguana\Elasticsearch\Model\Adapter\Index;


class Builder extends \Magento\Elasticsearch\Model\Adapter\Index\Builder
{
    /**
     * @return array
     */
    protected function getTokenizer()
    {
        $tokenizer = [
            'default_tokenizer' => [
                'type' => 'edge_ngram',
                'min_gram' => 2,
                'max_gram' => 30,
                'token_chars' => [
                    'letter',
                    'digit'
                ]
            ],
        ];
        return $tokenizer;
    }

    /**
     * @return array
     */
    protected function getCharFilter()
    {
        $charFilter = [
            'default_char_filter' => [
                'type' => 'html_strip',
                'escaped_tags' => ['(', ')']
            ],
        ];
        return $charFilter;
    }

}

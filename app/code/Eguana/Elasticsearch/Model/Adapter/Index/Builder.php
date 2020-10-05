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
     * {@inheritdoc}
     */
    public function build()
    {
        $tokenizer = $this->getTokenizer();
        $filter = $this->getFilter();
        $charFilter = $this->getCharFilter();

        $settings = [
            'analysis' => [
                'analyzer' => [
                    'default' => [
                        'type' => 'custom',
                        'tokenizer' => key($tokenizer),
                        'filter' => array_merge(
                            ['lowercase', 'keyword_repeat'],
                            array_keys($filter)
                        ),
                        'char_filter' => array_keys($charFilter)
                    ]
                ],
                'tokenizer' => $tokenizer,
                'filter' => $filter,
                'char_filter' => $charFilter,
            ],
            'max_ngram_diff' => '30'
        ];

        return $settings;
    }
    /**
     * @return array
     */
    protected function getTokenizer()
    {
        $tokenizer = [
            'default_tokenizer' => [
                'type' => 'ngram',
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

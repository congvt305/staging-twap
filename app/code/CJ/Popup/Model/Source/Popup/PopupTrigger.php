<?php

namespace CJ\Popup\Model\Source\Popup;

class PopupTrigger extends \Magenest\Popup\Model\Source\Popup\PopupTrigger
{
    public const CLICK_BACK = 5;
    public const IDLE_X_SECONDS_ON_PAGE = 6;
    public const SWITCH_TAB = 7;

    public static function getOptionArray()
    {
        return [
            \Magenest\Popup\Model\Popup::X_SECONDS_ON_PAGE => __('After customers spend X seconds on page'),
            \Magenest\Popup\Model\Popup::SCROLL_PAGE_BY_Y_PERCENT => __('After customers scroll page by X percent'),
            \Magenest\Popup\Model\Popup::VIEW_X_PAGE => __('After customers view X pages'),
            \Magenest\Popup\Model\Popup::EXIT_INTENT => __('Exit intent (When cursor moves outside the page)'),
            self::CLICK_BACK => __('When customer click Back button on web browser'),
            self::IDLE_X_SECONDS_ON_PAGE => __('When customer idle X seconds on page'),
            self::SWITCH_TAB => __('when the customer switches from the tab of our website')
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}

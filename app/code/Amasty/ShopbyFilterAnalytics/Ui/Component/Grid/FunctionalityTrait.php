<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Ui\Component\Grid;

use Amasty\ShopbyFilterAnalytics\Model\FunctionalityManager;

trait FunctionalityTrait
{
    /**
     * @var FunctionalityManager
     */
    private $functionalityManager;

    public function getData($key = '', $index = null)
    {
        if ($this->functionalityManager->isPremActive()) {
            return parent::getData($key, $index);
        }

        if ($key === '') {
            return [];
        }

        return null;
    }

    public function render()
    {
        if ($this->functionalityManager->isPremActive()) {
            return parent::render();
        }

        return '';
    }

    public function prepare()
    {
        if ($this->functionalityManager->isPremActive()) {
            parent::prepare();
        }
    }
}

<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Ui\Component;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\MassAction as MassActionAlias;

/**
 * Class MassAction
 *
 * Eguana\Faq\Ui\Component
 */
class MassAction extends MassActionAlias
{

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var string[]
     */
    private $allowedAction = ['delete'];

    /**
     * MassAction constructor.
     * @param ContextInterface $context
     * @param AuthorizationInterface $authorization
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        AuthorizationInterface $authorization,
        $components = [],
        array $data = []
    ) {
        $this->authorization = $authorization;
        parent::__construct($context, $components, $data);
    }

    /**
     * prepare method
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getConfiguration();

        $allowedActions = [];
        foreach ($config['actions'] as $action) {
            if (in_array($action['type'], $this->allowedAction)) {
                $allowedActions[] = $action;
            }
        }
        $config['actions'] = $allowedActions;

        $this->setData('config', (array)$config);
    }
}

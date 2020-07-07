<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 3/7/20
 * Time: 12:50 PM
 */
namespace Eguana\VideoBoard\Ui\Component;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\MassAction as MassActionAlias;

/**
 * Class for doing multiple transactions
 *
 * Class MassAction
 */
class MassAction extends MassActionAlias
{

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var array
     */
    protected $allowedAction = ['delete'];

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
     * prepare the layout for this class
     * @return $this|Template
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

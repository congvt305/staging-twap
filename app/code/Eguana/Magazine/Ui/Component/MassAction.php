<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 6:12 AM
 */
namespace Eguana\Magazine\Ui\Component;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class for doing multiple transactions
 *
 * Class MassAction
 */
class MassAction extends \Magento\Ui\Component\MassAction
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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

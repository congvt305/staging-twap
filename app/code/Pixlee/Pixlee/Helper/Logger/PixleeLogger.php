<?php
namespace Pixlee\Pixlee\Helper\Logger;

class PixleeLogger extends \Monolog\Logger
{
    public function __construct(
        \Pixlee\Pixlee\Helper\Logger\Handler $handler
    ) {
        parent::__construct("PixleeLogger", [$handler]);
    }

    public function addInfo($message, array $context = [])
    {
        $this->info($message, $context);
    }

    public function addWarning($message, array $context = [])
    {
        $this->warning($message, $context);
    }

    public function addError($message, array $context = [])
    {
        $this->error($message, $context);
    }
}

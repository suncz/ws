<?php

namespace console\swooleService;

abstract class ServerBase
{
    protected $webSocketServer;

    protected $serverIp;

    protected $config;

    protected $worker;

    protected $tasker;
    public $mqProducer;

    public function templateMethod()
    { // 模板方法 调用基本方法组装顶层逻辑
        $this->setServerIp();
        $this->setwebSocketServer();
        $this->start();
    }

    abstract public function setWorker();

    abstract protected function getWorker($workerId);

    abstract protected function setTasker();

    abstract protected function getTasker($workerId);

    abstract public function getWebSocketServer();

    abstract public function setWebSocketServer();

    abstract public function setServerIp();

    abstract public function getServerIp();

    abstract public function getConfig();

    abstract protected function createMQProcess($className, $num);

    abstract protected function initMQProcess();
    abstract protected function getMQProducer();
    abstract protected function start();
}

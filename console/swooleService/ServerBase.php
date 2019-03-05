<?php

namespace console\swooleService;

abstract class ServerBase
{
    protected $realServer;

    protected $serverId;

    protected $mq;

    protected $config;

    protected $worker;

    protected $tasker;

    public function templateMethod() { // 模板方法 调用基本方法组装顶层逻辑
        $this->setServerId();
        $this->setRealServer();
        $this->setWorker();
        $this->setTasker();
        $this->initMQProcess();
        $this->start();
    }
    abstract public function setWorker();
    abstract protected function getWorker($workerId);
    abstract protected function setTasker();
    abstract protected function getTasker($workerId);
    abstract public function getRealServer();
    abstract public function setRealServer();
    abstract public function setServerId();
    abstract public function getServerId();
    abstract public function getConfig();
    abstract protected function createMQProcess($className, $num);
    abstract protected function initMQProcess();
    abstract protected function start();
}

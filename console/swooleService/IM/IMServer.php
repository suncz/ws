<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/22
 * Time: 17:10
 */

namespace yii\console\swooleService\IM;

class IMServer extends \yii\console\swooleService\ServerBase
{
    public function __construct($config)
    {

    }

    public function start()
    {
        $this->_initMQProcess();

        $this->_server->start();
    }

    public function getServer()
    {
        return $this->_server;
    }

    public function getServerId()
    {
        return $this->_serverId;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    protected function _createMQProcess($className, $num)
    {
        for ($i = 0; $i < $num; ++$i) {
            $process = new swoole_process(function () use ($className) {
                (new $className($this))->process();
            });
            $this->_server->addProcess($process);
        }
    }

    protected function _getWorker()
    {

    }

    protected function _getTasker()
    {

    }

    protected function _initMQProcess()
    {

    }

    public function getMQ()
    {

    }
}
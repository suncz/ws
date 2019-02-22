<?php
namespace yii\console\swooleService;
abstract class ServerBase
{
    protected $_server;

    protected $_serverId;

    protected $_mq;

    protected $_config;

    private $_worker;

    private $_tasker;

    public function __construct($config)
    {
        $server = new swoole_websocket_server($config['ip'], $config['port']);
        $server->set($config['setting']);

        $this->_config = $config;

        $server->on('Start', function ($server) use ($config) {
            Logger::info('server start');
            swoole_set_process_name('php '.$config['serverName'].' master');
        });

        $server->on('ManagerStart', function ($server) use ($config) {
            Logger::info('ManagerStart');
            swoole_set_process_name('php '.$config['serverName'].' manager');
        });

        $server->on('ManagerStop', function ($server) {
            Logger::info('ManagerStop');
        });

        $server->on('WorkerStart', function ($server, $workerId) {
            Logger::info('worker start worker_id:'.$workerId);
            if ($workerId < $this->_config['setting']['worker_num']) {
                $this->_worker = $this->_getWorker();
            } else {
                $this->_tasker = $this->_getTasker();
            }
        });

        $server->on('WorkerError', function ($server, $workerId, $workerPid, $exitCode) {
            Logger::error('workerId:'.$workerId.' workerPid:'.$workerPid.' exitCode:'.$exitCode);
        });

        $server->on('WorkerStop', function ($server, $workerId) {
            Logger::info('workerId:'.$workerId.' stop');
        });

        $server->on('Open', function ($server, $req) {
            $this->_worker->onConnect($server, $req);
        });

        $server->on('Message', function ($server, $frame) {
            $this->_worker->onCmdMessage($server, $frame);
        });

        $server->on('PipeMessage', function ($server, $fromWorkerId, $message) {
            if ($server->worker_id < $this->_config['setting']['worker_num']) {
                $this->_worker->onPipeMessage($server, $fromWorkerId, $message);
            } else {
                $this->_tasker->onPipeMessage($server, $fromWorkerId, $message);
            }
        });

        $server->on('Close', function ($server, $fd) {
            $this->_worker->onDisconnect($server, $fd);
        });

        $server->on('Task', function ($server, $taskId, $fromId, $data) {
            $this->_tasker->onTask($server, $taskId, $fromId, $data);
        });

        //必须设置finish回调，不然启动不了
        $server->on('Finish', function ($server, $taskId, $data) {
        });

        $server->on('Shutdown', function ($server) {
            Logger::info('server shutdown');
        });

        $this->_server = $server;

        exec('ifconfig|grep -A 1 eth0|grep "inet addr"|awk \'{print $2}\'|awk -F : \'{print $2}\'', $out);
        if (!$out) {
            //centos
            exec('ifconfig|grep -A 1 eth0|grep "inet"|awk \'{print $2}\'', $out);
        }
        $this->_serverId = ip2long($out[0]);
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

    abstract protected function _getWorker();

    abstract protected function _getTasker();

    abstract protected function _initMQProcess();

    abstract public function getMQ();
}

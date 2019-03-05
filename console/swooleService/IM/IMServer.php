<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/22
 * Time: 17:10
 */

namespace console\swooleService\IM;

use console\swooleService\ServerBase;


class IMServer extends ServerBase
{

    public function __construct($config)
    {
        $this->config=$config;

    }

    public function setWorker()
    {
        // TODO: Implement setWorker() method.
        $worker=new IMWorker($this);
        $this->worker=$worker;
    }

    protected function getWorker($workerId)
    {
        swoole_set_process_name('php ws' . ' worker id:'.$workerId);
        return $this->worker;
    }

    public function setTasker()
    {
        // TODO: Implement setTasker() method.
        $this->tasker=new IMTasker($this);
    }

    protected function getTasker($workerId)
    {
        swoole_set_process_name('php ws' . ' tasker id:'.$workerId);
        return $this->tasker;
    }

    public function setRealServer()
    {
        $config = $this->config;

        $server = new \swoole_websocket_server($this->config['host'], $this->config['port']);
        $this->realServer=$server;
        $this->realServer->set($this->config['setting']);

        $server->on('Start', function ($server) use ($config) {
           print('server start'."\n");
            swoole_set_process_name('php ' . $config['serverName'] . ' master');
        });

        $server->on('ManagerStart', function ($server) use ($config) {
            print('ManagerStart'."\n");
            swoole_set_process_name('php ' . $config['serverName'] . ' manager');
        });

        $server->on('ManagerStop', function ($server) {
            print('ManagerStop'."\n");

        });

        $server->on('WorkerStart', function ($server, $workerId) {

            if ($workerId < $this->config['setting']['worker_num']) {
                print('worker start worker_id:' . $workerId."\n");
                $this->worker = $this->getWorker($workerId);
            } else {
                print('tasker start worker_id:' . $workerId."\n");
                $this->tasker = $this->getTasker($workerId);
            }
        });

        $server->on('WorkerError', function ($server, $workerId, $workerPid, $exitCode) {
            print('workerId:' . $workerId . ' workerPid:' . $workerPid . ' exitCode:' . $exitCode);
        });

        $server->on('WorkerStop', function ($server, $workerId) {
            print('workerId:' . $workerId . ' stop');
        });
        /*******************worker begin**************************/
        $server->on('Open', function ($server, $req) {
            $this->worker->onConnect($server, $req);
        });

        $server->on('Message', function ($server, $frame) {
            $this->worker->onCmdMessage($server, $frame);
        });
        /*******************worker end**************************/

        $server->on('Task', function ($server, $taskId, $fromId, $data) {
            $this->tasker->onTask($server, $taskId, $fromId, $data);
        });
        //必须设置finish回调，不然启动不了
        $server->on('Finish', function ($server, $taskId, $data) {
        });

        $server->on('PipeMessage', function ($server, $fromWorkerId, $message) {
            if ($server->worker_id < $this->config['setting']['worker_num']) {
                $this->worker->onPipeMessage($server, $fromWorkerId, $message);
            } else {
                $this->tasker->onPipeMessage($server, $fromWorkerId, $message);
            }
        });

        $server->on('Close', function ($server, $fd) {
            $this->worker->onDisconnect($server, $fd);
        });

        $server->on('Shutdown', function ($server) {
            print('server shutdown');
        });
    }

    public function getRealServer()
    {
        // TODO: Implement getRealServer() method.
    }

    public function start()
    {


        $this->realServer->start();
    }

    public function getServer()
    {
        return $this->server;
    }

    public function setServerId()
    {
        // TODO: Implement setServerId() method.
        exec('ifconfig|grep -A 1 eth0|grep "inet addr"|awk \'{print $2}\'|awk -F : \'{print $2}\'', $out);
        if (!$out) {
            //centos
            exec('ifconfig|grep -A 1 eth0|grep "inet"|awk \'{print $2}\'', $out);
        }
        if(!$out){
            exec('ifconfig|grep -A 1 ens33|grep "inet"|awk \'{print $2}\'', $out);
        }

        $this->serverId = isset($out[0])?$out[0]:'miss'.time();
    }

    public function getServerId()
    {
        return $this->serverId;
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function initMQProcess()
    {
        $this->createMQProcess('\console\swooleService\IM\IMMQCommonConsumerProcess',2);
    }
    protected function createMQProcess($className, $num)
    {
        for ($i = 0; $i < $num; ++$i) {
            $process = new \swoole_process(function () use ($className) {
                (new $className($this))->process();
            });
            $this->realServer->addProcess($process);
        }
    }

}
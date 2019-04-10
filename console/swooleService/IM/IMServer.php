<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/22
 * Time: 17:10
 */

namespace console\swooleService\IM;

use console\swooleService\Message;
use console\swooleService\ServerBase;
use IMMQProducer;
use Yii;
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

    public function setWebSocketServer()
    {
        $config = $this->config;
        $server = new \swoole_websocket_server($this->config['host'], $this->config['port']);
        $this->webSocketServer=$server;
        $this->webSocketServer->set($this->config['setting']);
        Message::$iMServer=$this;
        $server->on('Start', function ($server) use ($config) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[start] line:'.__LINE__);
           print('server start'."\n");
            echo "pid master is:".$server->master_pid."\n";
            swoole_set_process_name('php ' . $config['serverName'] . ' master');
        });

        $server->on('ManagerStart', function ($server) use ($config) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[ManagerStart] line:'.__LINE__);
            print('ManagerStart'."\n");
            echo "pid manager is:".$server->manager_pid."\n";
            swoole_set_process_name('php ' . $config['serverName'] . ' manager');
        });

        $server->on('ManagerStop', function ($server) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[ManagerStop] line:'.__LINE__);
            print('ManagerStop'."\n");

        });
//
        $server->on('WorkerStart', function ($server, $workerId) {

//            var_dump(get_included_files());
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[WorkerStart] line:'.__LINE__);
            if ($workerId < $this->config['setting']['worker_num']) {
                Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[worker start] line:'.__LINE__);
                print('worker start worker_id:' . $workerId."is taskerWorker".var_dump($server->taskworker)."\n");
                $this->setWorker();

            } else {
                Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[tasker start] line:'.__LINE__);
                print('tasker start worker_id:' . $workerId."is taskerWorker".var_dump($server->taskworker)."\n");
                $this->setTasker();
            }
        });
//
        $server->on('WorkerError', function ($server, $workerId, $workerPid, $exitCode) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[WorkerError] line:'.__LINE__);
            print('workerId:' . $workerId . ' workerPid:' . $workerPid . ' exitCode:' . $exitCode);
        });
//
        $server->on('WorkerStop', function ($server, $workerId) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[WorkerStop] line:'.__LINE__);
            print('workerId:' . $workerId . ' stop');
        });
        /*******************worker begin**************************/
        $server->on('Open', function ($server, $req) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[Open] line:'.__LINE__);
            $this->worker->onConnect($server, $req);
        });

        $server->on('Message', function ($server, $frame) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[Message] line:'.__LINE__);
            $this->worker->onCmdMessage($server, $frame);
        });
        /*******************worker end**************************/

        $server->on('Task', function ($server, $taskId, $fromId, $data) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[Task] line:'.__LINE__);
            $this->tasker->onTask($server, $taskId, $fromId, $data);
        });
        //必须设置finish回调，不然启动不了
        $server->on('Finish', function ($server, $taskId, $data) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[Finish] line:'.__LINE__);
        });

        $server->on('PipeMessage', function ($server, $fromWorkerId, $message) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[PipeMessage] line:'.__LINE__);
            if ($server->worker_id < $this->config['setting']['worker_num']) {
                $this->worker->onPipeMessage($server, $fromWorkerId, $message);
            } else {
                $this->tasker->onPipeMessage($server, $fromWorkerId, $message);
            }
        });

        $server->on('Close', function ($server, $fd) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[Close] line:'.__LINE__);
//                echo "client-{$fd} is closed\n";
            $this->worker->onDisconnect($server, $fd);
        });

        $server->on('Shutdown', function ($server) {
            Yii::info('[pid is:'.getmypid().']'.get_class().'->'.__FUNCTION__.'-'.'[Shutdown] line:'.__LINE__);
            print('server shutdown');
        });
        Yii::info(print_r($server,true));
    }

    public function getWebSocketServer()
    {
            return $this->webSocketServer;
    }

    public function start()
    {
        $this->initMQProcess();
        $this->webSocketServer->start();
    }



    public function setServerIp()
    {
        // TODO: Implement setserverIp() method.
        exec('ifconfig|grep -A 1 eth0|grep "inet addr"|awk \'{print $2}\'|awk -F : \'{print $2}\'', $out);
        if (!$out) {
            //centos
            exec('ifconfig|grep -A 1 eth0|grep "inet"|awk \'{print $2}\'', $out);
        }
        if(!$out){
            exec('ifconfig|grep -A 1 ens33|grep "inet"|awk \'{print $2}\'', $out);
        }

        $this->serverIp = isset($out[0])?$out[0]:'miss'.time();
    }

    public function getServerIp()
    {
        return $this->serverIp;
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function initMQProcess()
    {
        $this->createMQProcess('\console\swooleService\IM\IMMQMsgConsumerProcess',2);
    }
    protected function createMQProcess($className, $num)
    {
        for ($i = 0; $i < $num; ++$i) {
            $process = new \swoole_process(function () use ($className) {
                (new $className($this))->process();
            });
            $this->webSocketServer->addProcess($process);
        }
    }
    public function setMQProducer()
    {
        if (!$this->mqProducer) {
            $this->mqProducer = new IMMQProducer($this);
        }
    }


}
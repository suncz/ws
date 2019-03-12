<?php

namespace console\swooleService;

use console\swooleService\IM\IMServer;

abstract class WorkerBase
{

    //框架server对象
    protected $iMServer;

    //当前客户端唯一标示符
    protected $_cid;

    //当前网络句柄
    protected $_fd;

    public function __construct(IMServer $iMServer)
    {
        $this->iMServer = $iMServer;
        swoole_set_process_name('php ' . $iMServer->getConfig()['serverName'] . ' worker');
    }

    abstract public function onConnect($webSocketServer, $req);
    abstract public function onCmdMessage($webSocketServer, $frame);
    abstract public function onPipeMessage($webSocketServer, $fromWorkerId, $message);
    abstract public function onDisconnect($webSocketServer, $fd);

    protected function _getCid($fd) {
        return $this->iMServer->getserverIp(). '_' . $fd;
    }

}
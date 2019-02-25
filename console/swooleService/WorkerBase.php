<?php

namespace console\swooleService;

abstract class WorkerBase
{

    //框架server对象
    protected $_server;

    //当前客户端唯一标示符
    protected $_cid;

    //当前网络句柄
    protected $_fd;

    public function __construct($server)
    {
        $this->_server = $server;
        swoole_set_process_name('php ' . $server->getConfig()['serverName'] . ' worker');
    }

    abstract public function onConnect($server, $req);
    abstract public function onCmdMessage($server, $frame);
    abstract public function onPipeMessage($server, $fromWorkerId, $message);
    abstract public function onDisconnect($server, $fd);

    protected function _getCid($fd) {
        return $this->_server->getServerId(). '_' . $fd;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/25
 * Time: 10:19
 */

namespace console\swooleService;
abstract class TaskerBase
{
    protected $_server;

    public function __construct ($server)
    {
        $this->_server = $server;
        swoole_set_process_name('php ws'  . ' tasker');
    }

    abstract public function onTask($server, $taskId, $fromId, $data);

    abstract public function onPipeMessage($server, $fromWorkerId, $message);
}
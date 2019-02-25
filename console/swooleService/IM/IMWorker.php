<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/22
 * Time: 17:28
 */

namespace console\swooleService\IM;

use console\swooleService\WorkerBase;


class IMWorker extends WorkerBase
{

    public function __construct($server)
    {
        parent::__construct($server);

    }

    public function onConnect($server, $req)
    {
        echo __CLASS__.'->'.__FUNCTION__."\n";
    }

    public function onCmdMessage($server, $frame)
    {
        echo __CLASS__.'->'.__FUNCTION__."\n";

    }

    public function onPipeMessage($server, $fromWorkerId, $message)
    {
        echo __CLASS__.'->'.__FUNCTION__."\n";
    }

    public function onDisconnect($server, $fd)
    {
        echo __CLASS__.'->'.__FUNCTION__."\n";
    }




}

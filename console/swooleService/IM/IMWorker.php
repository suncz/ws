<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/22
 * Time: 17:28
 */

namespace yii\console\swooleService\IM;


class IMWorker extends Worker
{
    private $_appIds = array();
    private $msgId = '';

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

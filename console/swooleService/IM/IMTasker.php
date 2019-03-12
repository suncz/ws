<?php
namespace console\swooleService\IM;

use console\swooleService\TaskerBase;

class IMTasker extends TaskerBase
{
    public function __construct($server)
    {
        parent::__construct($server);
    }

    public function onTask($webSocketServer, $taskId, $fromId, $data)
    {
         while(1){
             echo "I am On task ,sleep(5)\b";
             sleep(5);
             echo "111 \n";
             break;
         }
    }
    public function onPipeMessage($webSocketServer, $fromWorkerId, $message)
    {
        echo __CLASS__.'->'.__FUNCTION__."\n";
    }
}

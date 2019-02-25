<?php
namespace console\swooleService\IM;

use console\swooleService\TaskerBase;

class IMTasker extends TaskerBase
{
    public function __construct($server)
    {
        parent::__construct($server);
    }

    public function onTask($server, $taskId, $fromId, $data)
    {
         while(1){
             sleep(10);
             echo "111 \n";
         }
    }
    public function onPipeMessage($server, $fromWorkerId, $message)
    {
    }
}

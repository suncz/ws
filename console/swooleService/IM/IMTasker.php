<?php
namespace yii\console\swooleService\IM;

class IMTasker extends Tasker
{
    public function __construct($server)
    {
        parent::__construct($server);

        IMTools::setIMServer($server);
        Activity::init($server);
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

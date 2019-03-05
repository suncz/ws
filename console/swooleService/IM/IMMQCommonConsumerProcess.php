<?php
namespace console\swooleService\IM;
use console\swooleService\MQBase;
/* 通用消息处理进程
 * 所有server只有一个进程会受到消息.
 */
class IMMQCommonConsumerProcess extends MQBase
{

    public function handle($data)
    {
        print_r($data);
            echo 'handle; \n';
    }


    protected function _getQueueName()
    {
        return 'im_msg_common_queue';
    }

    protected function _getExchangeName()
    {
        return 'im_msg_common';
    }

    protected function _getExchangeType()
    {
        return 'direct';
    }
}

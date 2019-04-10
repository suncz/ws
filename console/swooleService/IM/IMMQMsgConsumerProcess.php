<?php
namespace console\swooleService\IM;
use console\swooleService\MQBase;
/* 通用消息处理进程
 * 所有server只有一个进程会受到消息.
 */
class IMMQMsgConsumerProcess extends MQBase
{

    public $queueConfig=[
        'queueName'=>'sunny_queue_msg_',
        'relationExchange'=>[
            [
                'exchangeName'=>'sunny_im_broadcast',
                'exchangeType'=>'fanout',
                'durable'=>false,
                'routingKey'=>''
            ],
            [
                'exchangeName'=>'sunny_im_point_to_point',
                'exchangeType'=>'direct',
                'durable'=>false,
                'routingKey'=>'ip'
            ],
        ],
    ];
    public function __construct(IMServer $server)
    {
        $this->queueConfig['queueName']= $this->queueConfig['queueName'].$server->getServerIp();
        foreach ($this->queueConfig['relationExchange'] as $key => $exchangeInfo){
            if($exchangeInfo['routingKey']){
                if($exchangeInfo['routingKey']=='ip'){
                    $this->queueConfig['relationExchange'][$key]['routingKey']=$exchangeInfo['routingKey'].$server->getServerIp();
                }
            }
        }
        parent::__construct($server);
    }

    public function handle($data)
    {
        echo "handle pid is".getmypid()."\n";
         print_r($data);
         echo "handle; \n";
    }


}

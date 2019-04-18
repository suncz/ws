<?php

namespace console\swooleService\IM;

use console\swooleService\Message;
use console\swooleService\MQConsumerBase;
use Yii;

/* 通用消息处理进程
 * 所有server只有一个进程会受到消息.
 */

class IMMQMsgConsumerProcess extends MQConsumerBase
{

    public $queueConfig = [
        'queueName' => 'sunny_queue_msg_{ip}',
        'broadcastExchange' => 'sunny_im_broadcast',
        'pointToPointExchange' => 'sunny_im_point_to_point',
        'relationExchange' => [
            [
                'exchangeName' => 'sunny_im_broadcast',
                'exchangeType' => 'fanout',
                'durable' => false,
                'routingKey' => ''
            ],
            [
                'exchangeName' => 'sunny_im_point_to_point',
                'exchangeType' => 'direct',
                'durable' => false,
                'routingKey' => 'ip'
            ],
        ],
    ];
//    public $queueConfig;

    public function __construct(IMServer $server)
    {
        $this->queueConfig = Yii::$app->params['mQMsgConsume'];
        $this->queueConfig['queueName'] = str_replace('{ip}', $server->getServerIp(), $this->queueConfig['queueName']);
        foreach ($this->queueConfig['relationExchange'] as $key => $exchangeInfo) {
            if ($exchangeInfo['routingKey']) {
                if ($exchangeInfo['routingKey'] == 'ip') {
                    $this->queueConfig['relationExchange'][$key]['routingKey'] = $exchangeInfo['routingKey'] . $server->getServerIp();
                }
            }
        }
        parent::__construct($server);
    }

    public function handle($data)
    {
        echo "handle pid is" . getmypid() . "\n";
        print_r($data);
        $cmd = $data['cmd'];
        $handleData = $data['data'];
        if ($cmd == 'roomBroadcast') {
            $this->roomBroadcast($handleData['rid'], $handleData['sendData']);
        } elseif ($cmd == 'broadcast') {
            $this->broadcast($handleData['sendData']);
        }
        echo "handle; \n";
    }

    public function roomBroadcast($rid, $sendData)
    {
        Message::sendMessageRoomLoginer($rid, json_encode($sendData));
    }

    public function broadcast($sendData)
    {
        Message::sendMessageToAll(json_encode($sendData));
    }


}

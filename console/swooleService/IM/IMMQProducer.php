<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/10
 * Time: 17:15
 */

namespace console\swooleService\IM;

use console\swooleService\IM\libs\IMRedisKey;
use console\swooleService\MQProducerBase;
use PhpAmqpLib\Message\AMQPMessage;

class IMMQProducer extends MQProducerBase
{
    public $exchangeConfig = [
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
    ];
    //点对点定向发送
    public function sendTo($uid, $data)
    {
        $user = Yii::$app->redis->hget(IMRedisKey::getUserHashKey($uid));
        if (!$user) {


            return;
        }

        if (!isset($user['serverId'])) {
            return;
        }

        $serverId = $user['serverId'];

        $msg = json_encode(array(
                'cmd' => 'sendToUsers',
                'data' => array('uid' => array($uid), 'sendData' => $data),
            )
        );

        Logger::debug('product sendTo cid:'.$uid.' data:'.json_encode($data));

        $this->mqChannel->basic_publish(new AMQPMessage($msg), 'sunny_im_point_to_point');
    }

    public function broadcast($data)
    {
        $msg = json_encode(array(
                'cmd' => 'broadcast',
                'data' => array('sendData' => $data),
            )
        );

        Logger::debug('produce broadcast data:'.$msg);

        $this->mqChannel->basic_publish(new AMQPMessage($msg), 'sunny_im_broadcast');
    }

    public function roomBroadcast($rid, $data)
    {
        $msg = json_encode(array(
                'cmd' => 'roomBroadcast',
                'data' => array('rid' => $rid, 'sendData' => $data),
            )
        );
        $this->mqChannel->basic_publish(new AMQPMessage($msg),'sunny_im_broadcast');
    }
}
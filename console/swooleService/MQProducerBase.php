<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/10
 * Time: 17:10
 */

namespace console\swooleService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MQProducerBase
{
    protected $server;

    protected $mqConnection;
    protected $mqChannel;

    public function __construct($server)
    {
        $this->server = $server;

        $this->mqConnection = new AMQPStreamConnection('10.0.5.179', 5672, 'guest', 'guest');
        $this->mqChannel = $this->mqConnection->channel();
    }
    public function exchangeDeclare(){

    }
    /**
     * 消息投递
     */
    public function send($exchange, $queue, $data, $deliveryMode = 1)
    {
        $this->mqChannel->basic_publish(new AMQPMessage($data, array('delivery_mode' => $deliveryMode)), $exchange, $queue);
    }
}

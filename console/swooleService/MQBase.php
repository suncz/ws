<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/25
 * Time: 10:20
 */

namespace console\swooleService;

use PhpAmqpLib\Connection\AMQPStreamConnection;

abstract class MQBase
{
    protected $_server;

    protected $_mqConnection;
    protected $_mqChannel;

    protected $_stop;
    private $_isProcessing;

    protected $_maxLoop = 10;
    protected $_currentLoop = 0;
    public $queueConfig;

    public function __construct($server)
    {
        $this->_server = $server;

        $this->_mqConnection = new AMQPStreamConnection('10.0.5.179', 5672, 'guest', 'guest');
        $this->_mqChannel = $this->_mqConnection->channel();

        $this->_maxLoop = 10;
    }

    //queue设置为自动删除
    //消息投递到exchange，不直接投递到queue
    //如果没有queue绑定到exchange则丢弃，避免消息堆积
    //fanout类型的消息可以投递到多个queue direct只能投递到一个queue
    public function process()
    {
        $className = get_class($this);
        swoole_set_process_name('php ws' . ' ' . $className);
        pcntl_signal(SIGUSR1, function ($signo) use ($className) {
            Logger::info('stopping ' . $className . posix_getpid());
            $this->_stop = true;

            //如果不在处理，则立即停止
            if (!$this->_isProcessing) {
                $this->_mqChannel->close();
                $this->_mqConnection->close();
                Logger::info($className . '(' . posix_getpid() . ') stopped');
                exit;
            }
        });

        //队列连接断开自动删除
        $this->_mqChannel->queue_declare($this->queueConfig['queueName']);
        foreach ($this->queueConfig['relationExchange'] as $exchangeInfo) {
            print_r($exchangeInfo);
            $this->_mqChannel->exchange_declare($exchangeInfo['exchangeName'], $exchangeInfo['exchangeType'], false, $exchangeInfo['durable'], false);
            $this->_mqChannel->queue_bind($this->queueConfig['queueName'], $exchangeInfo['exchangeName'],$exchangeInfo['routingKey']);
        }


        print_r($className . '(' . posix_getpid() . ') consume queue of ' . $this->queueConfig['queueName']);

        $this->_mqChannel->basic_consume($this->queueConfig['queueName'], '', false, false, false, false, function ($message) use ($className) {

            $this->_isProcessing = true;

            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

            $data = json_decode($message->body, true);

            $this->handle($data);

            $this->_isProcessing = false;
        });

        while (true) {
            $this->_mqChannel->wait();
            if ($this->_stop) {
                break;
            }

            ++$this->_currentLoop;
            echo "max loop is:".$this->_maxLoop."\n";
            echo "current loop is:".$this->_currentLoop."\n";
            if ($this->_maxLoop && $this->_currentLoop >= $this->_maxLoop) {
                break;
            }
        }

        $this->_mqChannel->close();
        $this->_mqConnection->close();

        print_r($className . '(' . posix_getpid() . ') stopped');
    }

    abstract public function handle($data);
}

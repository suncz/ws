<?php


abstract class MQConsumerProcess
{
    protected $_server;

    protected $_mqConnection;
    protected $_mqChannel;

    protected $_stop;
    private $_isProcessing;

    protected $_maxLoop = 0;
    protected $_currentLoop = 0;

    public function __construct($server)
    {
        $this->_server = $server;

        Config::reload();

        $this->_mqConnection = new AMQPStreamConnection(Config::getConf('mqServer')['ip'], Config::getConf('mqServer')['port'], Config::getConf('mqServer')['username'], Config::getConf('mqServer')['password']);
        $this->_mqChannel = $this->_mqConnection->channel();

        $this->_maxLoop = Config::getConf('server')['mqProcessMaxLoop'];

    }

    //queue设置为自动删除
    //消息投递到exchange，不直接投递到queue
    //如果没有queue绑定到exchange则丢弃，避免消息堆积
    //fanout类型的消息可以投递到多个queue direct只能投递到一个queue
    public function process()
    {
        $className = get_class($this);
        swoole_set_process_name('php '.$this->_server->getConfig()['serverName'].' '.$className);

        pcntl_signal(SIGUSR1, function ($signo) use ($className) {
            Logger::info('stopping '.$className.posix_getpid());
            $this->_stop = true;

            //如果不在处理，则立即停止
            if (!$this->_isProcessing) {
                $this->_mqChannel->close();
                $this->_mqConnection->close();
                Logger::info($className.'('.posix_getpid().') stopped');
                exit;
            }
        });

        //队列连接断开自动删除
        $this->_mqChannel->queue_declare($this->_getQueueName());
        $this->_mqChannel->exchange_declare($this->_getExchangeName(), $this->_getExchangeType(), false, false, false);
        $this->_mqChannel->queue_bind($this->_getQueueName(), $this->_getExchangeName());

        Logger::info($className.'('.posix_getpid().') consume queue of '.$this->_getQueueName());

        $this->_mqChannel->basic_consume($this->_getQueueName(), '', false, false, false, false, function ($message) use ($className) {

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
            if ($this->_maxLoop && $this->_currentLoop >= $this->_maxLoop) {
                break;
            }
        }

        $this->_mqChannel->close();
        $this->_mqConnection->close();

        Logger::info($className.'('.posix_getpid().') stopped');
    }

    abstract public function handle($data);
    abstract protected function _getQueueName();
    abstract protected function _getExchangeName();
    abstract protected function _getExchangeType();
}

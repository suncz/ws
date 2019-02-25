<?php
namespace scriptmanage\websocket\server;
use Yii;
use swoole_websocket_server;

/**
 * 长连接
 *
 * Class WebSocketServer
 * @package console\controllers
 */
class WebSocketServer
{
    protected $_host;

    protected $_port;

    protected $_mode;

    protected $_socketType;

    protected $_type;

    protected $_config;

    /**
     * 服务
     *
     * @var
     */
    protected $_server;

    /**
     * WebSocket constructor.
     * @param $host
     * @param $port
     * @param $config
     */
    public function __construct($host, $port, $mode, $socketType, $type, $config)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_mode = $mode;
        $this->_socketType = $socketType;
        $this->_type = $type;
        $this->_config = $config;
    }

    /**
     * 启动进程
     */
    public function run()
    {
        if($this->_type == 'ws')
        {
            $this->_server = new swoole_websocket_server($this->_host, $this->_port, $this->_mode, $this->_socketType);
        }
        else
        {
            $this->_server = new swoole_websocket_server($this->_host, $this->_port, $this->_mode, $this->_socketType | SWOOLE_SSL);
        }
        $this->script=['second'=>5,'cmd'=>'/usr/local/php/bin/php /home/wwwroot/tchat.me/web/protected/yiic rank starta'];
        swoole_set_process_name('php '.'script manange websocket  master');
        $this->_server->set($this->_config);
        $this->_server->on('open', [$this, 'onOpen']);
        $this->_server->on('WorkerStart', [$this, 'onWorkStart']);
        $this->_server->on('message', [$this, 'onMessage']);
        $this->_server->on('task', [$this, 'onTask']);
        $this->_server->on('finish', [$this, 'onFinish']);
        $this->_server->on('close', [$this, 'onClose']);
        //创建处理脚本的进程
        $process = new swoole_process(function ()  {
            swoole_set_process_name('php getScript');
            
        });
        $this->_server->addProcess($process);
        $this->_server->start();

    }

    /**
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2018/12/25 16:54
     */
    public function onWorkStart($serv, $worker_id){
        global $argv;
        if($worker_id >= $serv->setting['worker_num']) {
            swoole_set_process_name("php {$argv[0]} task worker");
        } else {
            swoole_set_process_name("php {$argv[0]} event worker");
        }

    }
    /**
     * 开启连接
     *
     * @param $server
     * @param $frame
     */
    public function onOpen($server, $frame)
    {
        echo "server: handshake success with fd{$frame->fd}\n";
        echo "server: {$frame->data}\n";

        // 验证token进行连接判断
    }

    /**
     * 消息
     * @param $server
     * @param $frame
     * @throws \Exception
     */
    public function onMessage($server, $frame)
    {
        if (!($message = json_decode($frame->data, true)))
        {
            echo "没有消息内容" . PHP_EOL;
            return true;
        }

        // 输出调试信息
        echo $frame->data . PHP_EOL;
        // 私发
        $server->push($frame->fd, $this->singleMessage($message['type'], $frame->fd, $message['to_client_id'],[
            'content' => nl2br(htmlspecialchars($message['content'])),
        ]));

        return true;
    }

    /**
     * 关闭连接
     *
     * @param $server
     * @param $fd
     */
    public function onClose($server, $fd)
    {
        echo "client {$fd} closed". PHP_EOL;


    }

    /**
     * 处理异步任务
     *
     * @param $server
     * @param $task_id
     * @param $from_id
     * @param $data
     */
    public function onTask($server, $task_id, $from_id, $data)
    {
        echo "新 AsyncTask[id=$task_id]" . PHP_EOL;
        $server->finish($data);
    }

    /**
     * 处理异步任务的结果
     *
     * @param $server
     * @param $task_id
     * @param $data
     */
    public function onFinish($server, $task_id, $data)
    {
        // 根据 fd 下发房间通知
        $sendData = json_decode($data, true);

        echo "AsyncTask[$task_id] 完成: $data" . PHP_EOL;
    }

    /**
     * 群发消息
     *
     * @param $type
     * @param $from_client_id
     * @param array $otherArr
     * @return string
     */
    protected function massMessage($type, $from_client_id,array $otherArr = [])
    {
        $message = array_merge([
            'type' => $type,
            'from_client_id'=> $from_client_id,
            'to_client_id' => 'all',
            'time'=> date('Y-m-d H:i:s'),
        ], $otherArr);

        return json_encode($message);
    }

    /**
     * 单发消息
     *
     * @param $type
     * @param $from_client_id
     * @param $to_client_id
     * @param array $otherArr
     * @return string
     */
    protected function singleMessage($type, $from_client_id, $to_client_id,array $otherArr = [])
    {
        $message = array_merge([
            'type' => $type,
            'from_client_id'=> $from_client_id,
            'to_client_id' => $to_client_id,
            'time'=> date('Y-m-d H:i:s'),
        ], $otherArr);

        return json_encode($message);
    }
}
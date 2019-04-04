<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/22
 * Time: 17:28
 */

namespace console\swooleService\IM;

use console\swooleService\IM\libs\UserCenter;
use console\swooleService\Message;
use console\swooleService\MyTools;
use console\swooleService\WorkerBase;
use Exception;
use function function_exists;
use function var_dump;
use Yii;
use console\controllers\service;

use console\swooleService\IM\libs\Cmd;
use console\swooleService\IM\libs\Code;
use console\swooleService\IM\libs\IMRedisKey;

class IMWorker extends WorkerBase
{

    public $whenSocket = [
        'tourist' => [
            'outer' => false,
            'room' => true,
        ],
        'loginUser' => [
            'outer' => true,
            'room' => true,
        ]];

    public function __construct(IMServer $iMServer)
    {
        parent::__construct($iMServer);

    }

    public function onConnect($webSocketServer, $req)
    {
        $sid = trim(@$req->get['sid']);
        $uid = trim(@$req->get['uid']);
        $rid = trim(@$req->get['rid']);
        $mid = trim(@$req->get['mid']);
        if ($sid) {
            Yii::$app->session->setId($sid);
        }
        $fd = $req->fd;
        do {
            //游客
            try {

                $result = Yii::$app->runAction('service/user/connect', [$fd, $sid, $uid, $rid, $mid]);

                $isPass=$result->data['isPass'];
                if ($isPass == false) {
                    $webSocketServer->push($fd, IMResponse::wsOutput(Cmd::D_CONNET, IMErrors::CONNECT_FAIL));
                    $webSocketServer->after(2000, function () use ($fd) {
                        $this->iMServer->getWebSocketServer()->close($fd, true);
                    });
                } else {
                    $webSocketServer->push($fd, IMResponse::wsOutput(Cmd::D_CONNET, 200, 'ok'));
                }
            } catch (\Exception $e) {
                echo $e->getTraceAsString();
                Yii::error($e->getTraceAsString());
                break;
            }
        } while (false);


    }

    /**
     * @param $webSocketServer
     * @param $frame
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2019/3/26 14:29
     */
    public function onCmdMessage($webSocketServer, $frame)
    {
        try {
            echo $frame->data."\n";
            //检查通信协议格式
            $data = IMRequest::input($frame->data);
        } catch (Exception $e) {
            $data['cmd'] = Cmd::D_REQUEST_FAIL;
            Message::sendSingleMessage($frame->fd, IMResponse::wsOutput(Cmd::D_REQUEST_FAIL, $e->getCode(), $e->getMessage()));
            return;
        }
        try {
            //调用路由
            $jsonData['fromFd']=$frame->fd;
            $jsonData['data']=$data->d;
            Yii::$app->runAction('service/' . $data->cmd, [json_encode($jsonData)]);
        } catch (Exception $e) {
            Message::sendSingleMessage($frame->fd, IMResponse::wsOutput(Cmd::D_SYS_FAIL, $e->getCode(), $e->getMessage()));
        }
    }

    public function onPipeMessage($webSocketServer, $fromWorkerId, $message)
    {
        echo getmypid() . "---" . $webSocketServer->worker_id . "\n";
        echo __CLASS__ . '->' . __FUNCTION__ . "\n";
    }

    //断开连接清理用户信息
    public function onDisconnect($webSocketServer, $fd)
    {
        echo __CLASS__ . '->' . __FUNCTION__ . "\n";
        UserCenter::clear($fd);

    }


}

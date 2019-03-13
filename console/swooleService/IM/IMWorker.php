<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/22
 * Time: 17:28
 */

namespace console\swooleService\IM;

use console\swooleService\WorkerBase;
use Yii;
use console\controllers\service;

class IMWorker extends WorkerBase
{

    public $whenSocket = [
        'tourist' => [
            'outer' => true,
            'room' => true,
        ],
        'loginUser' => [
            'outer' => true,
            'room' => true,
        ]];
    public function __construct(IMServer $imServer)
    {
        parent::__construct($imServer);

    }

    public function onConnect($webSocketServer, $req)
    {
        $sid = trim(@$req->get['sid']);
        $uid = trim(@$req->get['uid']);
        $rid = trim(@$req->get['rid']);
        $mid = trim(@$req->get['mid']);

        $fd=$req->fd;

        Yii::$app->runAction('service/user/outer-tourist');
        $webSocketServer->push($req->fd,json_encode([1,2,4]));
        $webSocketServer->after(200, function () use ($fd) {
                    $this->iMServer->getWebSocketServer()->close($fd, true);
        });
        //游客允许全站登陆
//        if($this->whenSocket['tourist']['outer']){
//            if(!$uid && !$rid && !$mid){
//                Yii::$app->runAction('/service/user/outerTourist');
//            }else{
//                $data['code']=1001;
//                $data['msg']='connect error';
//                $webSocketServer->push($req->fd,json_encode($data));
//                if (!$webSocketServer->exist($req->fd)) {
//                    return;
//                }
//                $webSocketServer->after(200, function () use ($fd) {
//                    $webSocketServer->close($fd, true);
//                });
//            }
//
//        }


    }

    public function onCmdMessage($webSocketServer, $frame)
    {

        echo __CLASS__ . '->' . __FUNCTION__ . "\n";

    }

    public function onPipeMessage($webSocketServer, $fromWorkerId, $message)
    {
        echo getmypid() . "---" . $webSocketServer->worker_id . "\n";
        echo __CLASS__ . '->' . __FUNCTION__ . "\n";
    }

    public function onDisconnect($webSocketServer, $fd)
    {
        echo __CLASS__ . '->' . __FUNCTION__ . "\n";
    }


}

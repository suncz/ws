<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/22
 * Time: 17:28
 */

namespace console\swooleService\IM;

use console\models\bll\UserBll;
use console\models\common\Cmd;
use console\models\common\Code;
use console\models\common\RedisKey;
use console\swooleService\WorkerBase;
use console\libs\Tools;
use Yii;
use console\controllers\service;

class IMWorker extends WorkerBase
{


    static public $serverIp;

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
        if($sid){
            Yii::$app->session->setId($sid);
        }

        $fd = $req->fd;
        do {
            service\UserController::$imServer = $this->iMServer;
            //游客
            try {
                $isPass=UserBll::connectCanPass($fd,$sid,$uid,$rid);
                if ($isPass === false) {
                    $webSocketServer->push($fd, Tools::wsOutput(Cmd::D_CONNET, Code::CONNECT_FAIL));
                    $webSocketServer->after(200, function () use ($fd) {
                        $this->iMServer->getWebSocketServer()->close($fd, true);
                    });
                }else{
                    $webSocketServer->push($fd, Tools::wsOutput(Cmd::D_CONNET, 200,'ok'));
                }
            } catch (\Exception $e) {
                print_r($e->getMessage());
                echo $e->getTraceAsString();
                break;
            }
        } while (false);


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
    //断开连接清理用户信息
    public function onDisconnect($webSocketServer, $fd)
    {
        echo __CLASS__ . '->' . __FUNCTION__ . "\n";

        $rid=Yii::$app->redisLocal->hget(RedisKey::ROOM_MAP_HASH,$fd);
        if($rid){//房间
            //删除房间登陆用户
            Yii::$app->redisLocal->hdel(RedisKey::roomMemberKey($rid),$fd);
            //删除房间游客
            Yii::$app->redisLocal->srem(RedisKey::getRoomTouristKey($rid),$fd);


            $memberJson=Yii::$app->redisLocal->hget(RedisKey::roomMemberKey($rid),$fd);
            $arrMember=json_decode($memberJson);
            if($arrMember){//房间登陆用户
                $uid=$arrMember['uid'];
                //删除用户信息
                Yii::$app->redisShare->del(RedisKey::getUserHashKey($uid));
            }
        }
        //删除房间外登陆用户
        Yii::$app->redisLocal->srem(RedisKey::OUTER_USER_SET,$fd);
        //删除roomMap
        Yii::$app->redisLocal->hdel(RedisKey::ROOM_MAP_HASH,$fd);
        //删除房间外游客
        Yii::$app->redisLocal->srem(RedisKey::OUT_TOURIST_SET,$fd);
        //删除在线房间列表用户
        Yii::$app->redisShare->zrem(RedisKey::ROOM_ONLINE_USER_ZSET,$fd);
    }


}

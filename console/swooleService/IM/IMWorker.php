<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/22
 * Time: 17:28
 */

namespace console\swooleService\IM;

use console\swooleService\MyTools;
use console\swooleService\WorkerBase;
use Exception;
use function function_exists;
use function var_dump;
use Yii;
use console\controllers\service;
use console\libs\Tools;
use console\swooleService\IM\libs\Cmd;
use console\swooleService\IM\libs\Code;
use console\swooleService\IM\libs\IMRedisKey;

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
            service\UserController::$iMServer = $this->iMServer;
            //游客
            try {
                $isPass=Yii::$app->runAction('service/user/connect',[$fd,$sid,$uid,$rid,$mid]);
                if ($isPass === false) {
                    $webSocketServer->push($fd, Tools::wsOutput(Cmd::D_CONNET, Code::CONNECT_FAIL));
                    $webSocketServer->after(200, function () use ($fd) {
                        $this->iMServer->getWebSocketServer()->close($fd, true);
                    });
                }else{
                    $webSocketServer->push($fd, Tools::wsOutput(Cmd::D_CONNET, 200,'ok'));
                }
            } catch (\Exception $e) {
                Yii::error($e->getTraceAsString());
                break;
            }
        } while (false);


    }

    public function onCmdMessage($webSocketServer, $frame)
    {
        try{
            //检查通信协议格式
            $data=MyTools::checkProtocol($frame['data']);
            //调用路由
            $re=Yii::$app->runAction('service/'.$data['cmd'],$data);
            //格式化输出
            $responseData= MyTools::response($data['cmd'],$re);
        }catch (Exception $e){
            $responseData= MyTools::response($data['cmd']??"unknown",$e->getCode());
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

        $rid=Yii::$app->redisLocal->hget(IMRedisKey::ROOM_MAP_HASH,$fd);
        if($rid){//房间
            //删除房间登陆用户
            Yii::$app->redisLocal->hdel(IMRedisKey::roomMemberKey($rid),$fd);
            //删除房间游客
            Yii::$app->redisLocal->srem(IMRedisKey::getRoomTouristKey($rid),$fd);


            $memberJson=Yii::$app->redisLocal->hget(IMRedisKey::roomMemberKey($rid),$fd);
            $arrMember=json_decode($memberJson);
            if($arrMember){//房间登陆用户
                $uid=$arrMember['uid'];
                //删除用户信息
                Yii::$app->redisShare->del(IMRedisKey::getUserHashKey($uid));
            }
        }
        //删除房间外登陆用户
        Yii::$app->redisLocal->srem(IMRedisKey::OUTER_USER_SET,$fd);
        //删除roomMap
        Yii::$app->redisLocal->hdel(IMRedisKey::ROOM_MAP_HASH,$fd);
        //删除房间外游客
        Yii::$app->redisLocal->srem(IMRedisKey::OUT_TOURIST_SET,$fd);
        //删除在线房间列表用户
        Yii::$app->redisShare->zrem(IMRedisKey::ROOM_ONLINE_USER_ZSET,$fd);
    }


}

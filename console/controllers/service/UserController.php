<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/11
 * Time: 10:57
 */

namespace console\controllers\service;

use console\models\common\Cmd;
use console\models\common\Code;
use console\models\common\RedisKey;
use libs\Tools;
use Yii;
use yii\console\Controller;

class UserController extends Controller
{
    static public $imServer;
    //游客房间外建立连接
    function actionOuterTourist($fd)
    {
        Yii::$app->redisLocal->sadd(RedisKey::OUT_TOURIST_SET, $fd);
    }

    //游客在房间内建立连接
    function actionRoomTourist($fd, $rid)
    {
        Yii::$app->redisLocal->sadd(RedisKey::getRoomTouristKey($rid), $fd);
        Yii::$app->redisLocal->hset(RedisKey::ROOM_MAP_HASH,$fd,$rid);
        return true;
    }

    //用户在房间外登陆
    function actionOuterLogin($sid, $uid, $fd)
    {
        if (!$sid || !$uid || !$fd ){
            return false;
        }
        if (Yii::$app->session->get('uid') != $uid) {
            return false;
        }
        $oldUserinfo=Yii::$app->redisShare->hGetAll(RedisKey::getUserHashKey($uid));
        //查看是否已存在连接数据，有则销毁
        if($oldUserinfo){
            if(self::$iMServer->getWebSocketServer()->exist($fd)){
                self::$iMServer->getWebSocketServer()->close($fd,true);
            }
            Yii::$app->redisShare->del(RedisKey::getUserHashKey($uid));
        }

        $userInfo['sid']=$sid;
        $userInfo['uid']=$uid;
        $userInfo['nickname']=Yii::$app->session->get('nickname');
        $userInfo['headPic']=Yii::$app->session->get('headPic');
        $userInfo['serverIp']=self::$iMServer->getServerIp();
        $userInfo['fd']=$fd;
        Yii::$app->redisLocal->sAdd(RedisKey::OUTER_USER_SET,$fd);
        Yii::$app->redisShare->hMSet(RedisKey::getUserHashKey($uid),$userInfo);
        return true;


    }

    //用户在房间外登陆
    function actionRoomLogin($sid, $uid, $fd,$rid)
    {
        if (!$sid || !$uid || !$fd ||$rid){
            return false;
        }
        if (Yii::$app->session->get('uid') != $uid) {
            return false;
        }
        $oldUserinfo=Yii::$app->redisShare->hGetAll(RedisKey::getUserHashKey($uid));
        //查看是否已存在连接数据，有则销毁
        if($oldUserinfo){
            $oldUserInfoJson=json_decode($oldUserinfo,true);
            $oldFd=$oldUserInfoJson['fd'];
            if(self::$iMServer->getWebSocketServer()->exist($oldFd)){
                self::$iMServer->getWebSocketServer()->push($fd,Tools::wsOutput(Cmd::D_CONNET,Code::CONNECT_OTHER));
                self::$iMServer->getWebSocketServer()->after(200, function () use ($fd) {
                    self::$iMServer->getWebSocketServer()->close($fd, true);
                });
            }
            Yii::$app->redisShare->del(RedisKey::getUserHashKey($uid));
        }
        $userInfo['sid']=$sid;
        $userInfo['uid']=$uid;
        $userInfo['nickname']=Yii::$app->session->get('nickname');
        $userInfo['headPic']=Yii::$app->session->get('headPic');
        $userInfo['serverIp']=self::$iMServer->getServerIp();
        $userInfo['fd']=$fd;
        $jsonUser=json_encode($userInfo);
        Yii::$app->redisLocal->hset(RedisKey::ROOM_MEMBER_HASH,$fd,$jsonUser);
        Yii::$app->redisLocal->hset(RedisKey::ROOM_MAP_HASH,$fd,$rid);
        Yii::$app->redisShare->hMSet(RedisKey::getUserHashKey($uid),$userInfo);
        return true;


    }

}

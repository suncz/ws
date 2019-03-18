<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/11
 * Time: 10:57
 */

namespace console\controllers\service;

use console\swooleService\IM\libs\Cmd;
use console\swooleService\IM\libs\Code;
use console\swooleService\IM\libs\IMRedisKey;
use console\libs\Tools;
use Yii;
use yii\console\Controller;

class UserController extends Controller
{
    static public $iMServer;

    //游客房间外建立连接
    function actionConnect($fd, $sid, $uid, $rid, $mid)
    {
        $isPass = false;
        if (!$sid && !$uid) { //游客
            if (!$rid) {//房间外游客
                if (Yii::$app->params['whenSocket']['tourist']['outer']) {
                    $isPass = Yii::$app->runAction('service/user/outer-tourist', [$fd]);
                }

            } else {//房间内游客
                if (Yii::$app->params['whenSocket']['tourist']['room']) {
                    $isPass = Yii::$app->runAction('service/user/room-tourist', [$fd, $rid]);
                }
            }
        } else if ($sid && $uid) { //登陆用户
            if (!$rid) {//房间外登陆
                if (Yii::$app->params['whenSocket']['tourist']['outer']) {
                    $isPass = Yii::$app->runAction('service/user/outer-login', [$sid, $uid, $fd]);
                }
            } else {//房间内登陆
                if (Yii::$app->params['whenSocket']['tourist']['room']) {
                    $isPass = Yii::$app->runAction('service/user/room-login', [$sid, $uid, $fd, $rid]);
                }
            }
        } else {
            $isPass = false;

        }
        return $isPass;
    }

    function actionOuterTourist($fd)
    {
        Yii::$app->redisLocal->sadd(IMRedisKey::OUT_TOURIST_SET, $fd);
    }


    //游客在房间内建立连接
    function actionRoomTourist($fd, $rid)
    {
        Yii::$app->redisLocal->sadd(IMRedisKey::getRoomTouristKey($rid), $fd);
        Yii::$app->redisLocal->hset(IMRedisKey::ROOM_MAP_HASH, $fd, $rid);
        return true;
    }

    //用户在房间外登陆
    function actionOuterLogin($sid, $uid, $fd)
    {
        if (!$sid || !$uid || !$fd) {
            return false;
        }
        if (Yii::$app->session->get('uid') != $uid) {
            return false;
        }
        $oldUserinfo = Yii::$app->redisShare->hGetAll(IMRedisKey::getUserHashKey($uid));
        //查看是否已存在连接数据，有则销毁
        if ($oldUserinfo) {
            if (self::$iMServer->getWebSocketServer()->exist($fd)) {
                self::$iMServer->getWebSocketServer()->close($fd, true);
            }
            Yii::$app->redisShare->del(IMRedisKey::getUserHashKey($uid));
        }

        $userInfo['sid'] = $sid;
        $userInfo['uid'] = $uid;
        $userInfo['nickname'] = Yii::$app->session->get('nickname');
        $userInfo['headPic'] = Yii::$app->session->get('headPic');
        $userInfo['serverIp'] = self::$iMServer->getServerIp();
        $userInfo['fd'] = $fd;
        Yii::$app->redisLocal->sAdd(IMRedisKey::OUTER_USER_SET, $fd);
        Yii::$app->redisShare->hMSet(IMRedisKey::getUserHashKey($uid), $userInfo);
        return true;


    }

    //用户在房间外登陆
    function actionRoomLogin($sid, $uid, $fd, $rid)
    {
        if (!$sid || !$uid || !$fd || $rid) {
            return false;
        }
        if (Yii::$app->session->get('uid') != $uid) {
            return false;
        }
        $oldUserinfo = Yii::$app->redisShare->hGetAll(IMRedisKey::getUserHashKey($uid));
        //查看是否已存在连接数据，有则销毁
        if ($oldUserinfo) {
            $oldUserInfoJson = json_decode($oldUserinfo, true);
            $oldFd = $oldUserInfoJson['fd'];
            if (self::$iMServer->getWebSocketServer()->exist($oldFd)) {
                self::$iMServer->getWebSocketServer()->push($fd, Tools::wsOutput(Cmd::D_CONNET, Code::CONNECT_OTHER));
                self::$iMServer->getWebSocketServer()->after(200, function () use ($fd) {
                    self::$iMServer->getWebSocketServer()->close($fd, true);
                });
            }
            Yii::$app->redisShare->del(IMRedisKey::getUserHashKey($uid));
        }
        $userInfo['sid'] = $sid;
        $userInfo['uid'] = $uid;
        $userInfo['nickname'] = Yii::$app->session->get('nickname');
        $userInfo['headPic'] = Yii::$app->session->get('headPic');
        $userInfo['serverIp'] = self::$iMServer->getServerIp();
        $userInfo['fd'] = $fd;
        $jsonUser = json_encode($userInfo);
        Yii::$app->redisLocal->hset(IMRedisKey::ROOM_MEMBER_HASH, $fd, $jsonUser);
        Yii::$app->redisLocal->hset(IMRedisKey::ROOM_MAP_HASH, $fd, $rid);
        Yii::$app->redisShare->hMSet(IMRedisKey::getUserHashKey($uid), $userInfo);
        return true;


    }

}

<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/11
 * Time: 10:57
 */

namespace console\controllers\service;

use console\swooleService\IM\IMErrors;
use console\swooleService\IM\IMResponse;
use console\swooleService\IM\libs\Cmd;
use console\swooleService\IM\libs\Code;
use console\swooleService\IM\libs\IMRedisKey;
use console\swooleService\IM\libs\UserCenter;
use console\swooleService\Message;

use Yii;

class UserController extends BaseController
{

    //游客房间外建立连接
    function actionConnect($fd, $sid, $uid, $rid, $mid)
    {
        $result = IMResponse::actOutPut(0, ['isPass' => false]);
        if (!$sid && !$uid) { //游客
            if (!$rid) {//房间外游客
                echo "outer tourist \n";
                if (Yii::$app->params['whenSocket']['tourist']['outer']) {
                    return Yii::$app->runAction('service/user/outer-tourist', [$fd]);
                }

            } else {//房间内游客
                echo "room tourist \n";
                if (Yii::$app->params['whenSocket']['tourist']['room']) {
                    return Yii::$app->runAction('service/user/room-tourist', [$fd, $rid]);
                }
            }
        } else if ($sid && $uid) { //登陆用户
            if (!$rid) {//房间外登陆
                echo "outer login \n";
                if (Yii::$app->params['whenSocket']['tourist']['outer']) {
                    return Yii::$app->runAction('service/user/outer-login', [$sid, $uid, $fd]);
                }
            } else {//房间内登陆
                echo "room login \n";
                if (Yii::$app->params['whenSocket']['tourist']['room']) {
                    return Yii::$app->runAction('service/user/room-login', [$sid, $uid, $fd, $rid]);
                }
            }
        }
        return $result;
    }

    function actionOuterTourist($fd)
    {
        UserCenter::outerTourist($fd);
        Message::sendMessageOuterTourist(IMResponse::wsOutput(Cmd::D_OUT_TOURIST, IMErrors::OK, 'tourist login success'));
        return IMResponse::actOutPut(0, ['isPass' => true]);

    }


    //游客在房间内建立连接
    function actionRoomTourist($fd, $rid)
    {

        UserCenter::roomTourist($rid, $fd);
        Message::sendMessageRoomTourist($rid, IMResponse::wsOutput(Cmd::D_ROOM_TOURIST, IMErrors::OK, 'tourist room login success'));
        return IMResponse::actOutPut(0, ['isPass' => true]);
    }

    //用户在房间外登陆
    function actionOuterLogin($sid, $uid, $fd)
    {
        if (!$sid || !$uid || !$fd) {
            return IMResponse::actOutPut(0, ['isPass' => false]);
        }

        $userInfo['uid'] = $uid;
        UserCenter::outerLogin($sid, $uid, $fd);
        Message::sendMessageOuterLoginer(IMResponse::wsOutput(Cmd::D_LOGIN, IMErrors::OK, 'login success'), $userInfo);
        return IMResponse::actOutPut(0, ['isPass' => true]);

    }

    //用户在房间外登陆
    function actionRoomLogin($sid, $uid, $fd, $rid)
    {
        if (!$sid || !$uid || !$fd || !$rid) {
            return IMResponse::actOutPut(0, ['isPass' => false]);

        }
        $oldUserinfo = Yii::$app->redisShare->hgetall(IMRedisKey::getUserHashKey($uid));
        var_dump($oldUserinfo);
        //查看是否已存在连接数据，有则销毁
        if ($oldUserinfo) {
            $oldFd = $oldUserinfo['fd'];
            //单点登录
            if (Message::$iMServer->getWebSocketServer()->exist($oldFd)) {
                Message::$iMServer->getWebSocketServer()->push($oldFd, IMResponse::wsOutput(Cmd::D_CONNET, IMErrors::CONNECT_OTHER));
                Message::$iMServer->getWebSocketServer()->after(200, function () use ($oldFd) {
                    Message::$iMServer->getWebSocketServer()->close($oldFd, true);
                });
            }
            Yii::$app->redisShare->del(IMRedisKey::getUserHashKey($uid));
        }

        $userInfo['uid'] = $uid;
        if(UserCenter::roomLogin($sid, $uid, $fd, $rid) == false){
            return IMResponse::actOutPut(0, ['isPass' => false]);
        }
        Message::sendMessageRoomLoginer($rid, IMResponse::wsOutput(Cmd::D_LOGIN, IMErrors::OK, 'login success', $userInfo));
        return IMResponse::actOutPut(0, ['isPass' => true]);


    }

}

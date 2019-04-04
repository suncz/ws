<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/2
 * Time: 16:34
 */

namespace console\swooleService\IM\libs;

use Yii;

class UserCenter
{
    const USER_TYPE_OUTER_LOGIN = 1;   //房间外部登入用户
    const USER_TYPE_ROOM_LOGIN = 2;//房间登录用户

    const USER_TYPE_OUTER_TOURIST = 3;//房间外游客
    const USER_TYPE_ROOM_TOURIST = 4;//房间游客


    static function outerTourist($fd)
    {
        Yii::$app->redisLocal->sadd(IMRedisKey::OUTER_TOURIST_SET, $fd);
    }


    //游客在房间内建立连接
    static function roomTourist($rid,$fd)
    {
        Yii::$app->redisLocal->sadd(IMRedisKey::getRoomTouristKey($rid), $fd);
        Yii::$app->redisLocal->hset(IMRedisKey::ROOM_MAP_HASH, $fd, $rid);

    }

    //用户在房间外登陆
    static function outerLogin($sid, $uid, $fd)
    {
        $oldUserinfo = Yii::$app->redisLocal->hGetAll(IMRedisKey::getUserHashKey($uid));
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
    }

    //用户在房间外登陆
    static function roomLogin($sid, $uid, $fd, $rid)
    {
        if (!$sid || !$uid || !$fd || $rid) {
            return IMResponse::actOutPut(0, ['isPass' => false]);
        }
        if (Yii::$app->session->get('uid') != $uid) {
            return IMResponse::actOutPut(0, ['isPass' => false]);

        }
        $oldUserinfo = Yii::$app->redisShare->hGetAll(IMRedisKey::getUserHashKey($uid));
        //查看是否已存在连接数据，有则销毁
        if ($oldUserinfo) {
            $oldUserInfoJson = json_decode($oldUserinfo, true);
            $oldFd = $oldUserInfoJson['fd'];
            //单点登录
            if (self::$iMServer->getWebSocketServer()->exist($oldFd)) {
                self::$iMServer->getWebSocketServer()->push($fd, IMResponse::wsOutput(Cmd::D_CONNET, IMErrors::CONNECT_OTHER));
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
    }
    public static function getRoomId($fd){
        return  Yii::$app->redisLocal->hget(IMRedisKey::ROOM_MAP_HASH, $fd);
    }
    /**清理用户信息
     * @param $fd
     * @param $userType
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2019/4/2 16:39
     */
    static function clear($fd)
    {
        $rid = Yii::$app->redisLocal->hget(IMRedisKey::ROOM_MAP_HASH, $fd);
        if ($rid) {//房间
            //删除房间登陆用户
            Yii::$app->redisLocal->hdel(IMRedisKey::roomMemberKey($rid), $fd);
            //删除房间游客
            Yii::$app->redisLocal->srem(IMRedisKey::getRoomTouristKey($rid), $fd);


            $memberJson = Yii::$app->redisLocal->hget(IMRedisKey::roomMemberKey($rid), $fd);
            $arrMember = json_decode($memberJson);
            if ($arrMember) {//房间登陆用户
                $uid = $arrMember['uid'];
                //删除用户信息
                Yii::$app->redisShare->del(IMRedisKey::getUserHashKey($uid));
            }
        }
        //删除房间外登陆用户
        Yii::$app->redisLocal->srem(IMRedisKey::OUTER_USER_SET, $fd);
        //删除roomMap
        Yii::$app->redisLocal->hdel(IMRedisKey::ROOM_MAP_HASH, $fd);
        //删除房间外游客
        Yii::$app->redisLocal->srem(IMRedisKey::OUTER_TOURIST_SET, $fd);
        //删除在线房间列表用户
        Yii::$app->redisShare->zrem(IMRedisKey::ROOM_ONLINE_USER_ZSET, $fd);

    }
}
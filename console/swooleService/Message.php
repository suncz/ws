<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/18
 * Time: 11:51
 */

namespace console\swooleService;

use console\swooleService\IM\IMResponse;
use console\swooleService\IM\IMServer;
use console\swooleService\IM\libs\IMRedisKey;
use console\swooleService\IM\libs\UserCenter;
use Yii;

class Message
{
    public static $iMServer;


    public function __construct(IMServer $iMServer)
    {
        if (self::$iMServer == null) {
            self::$iMServer = $iMServer;
        }
    }

    /**
     * 发送点对点的消息
     * @param $toFd
     * @param $message
     * @param $isDelayClose
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2019/3/18 11:56
     * @return bool
     */
    static function sendSingleMessage($toFd, $jsonMessage, $isDelayClose = false)
    {

        if (!self::$iMServer->getWebSocketServer()->exist($toFd)) {
            UserCenter::clear($toFd);
            return false;
        }
        self::$iMServer->getWebSocketServer()->push($toFd, $jsonMessage);
        if ($isDelayClose) {
            self::$iMServer->getWebSocketServer()->close($toFd);
        }
        return true;

    }


    /**
     * 房间内对登陆用户进行广播
     * @param $roomId
     * @param $message
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2019/3/18 12:12
     */
    static function sendRoomMessage($roomId, $jsonMessage)
    {
        $key = IMRedisKey::roomMemberKey($roomId);
        $roomMember = Yii::$app->redisLocal->hgetall($key);
        print_r($roomMember);
        foreach ($roomMember as $v) {
            $arr = json_decode($v);
            $fd = $v['fd'];
            if (!self::$iMServer->getWebSocketServer()->exist($v['fd'])) {
                Yii::info('fd not exist' . $v);
                UserCenter::clear($v['fd']);
                continue;
            } else {
                self::$iMServer->getWebSocketServer()->push($fd, $jsonMessage);

            }
        }
    }

    static function sendRoomTouristMessage($roomId, $jsonMessage)
    {
        $touristFds = Yii::$app->redisLocal->smembers(IMRedisKey::getRoomTouristKey($roomId));
        var_dump($touristFds);
        foreach ($touristFds as $fd) {
            if (!self::$iMServer->getWebSocketServer()->exist($fd)) {
                echo 'tourist fd not exist' . $fd."\n";
                Yii::info('tourist fd not exist' . $fd);
                UserCenter::clear($fd);
                continue;
            } else {
                echo 'tourist fd :' . $fd;
                self::$iMServer->getWebSocketServer()->push($fd, $jsonMessage);

            }
        }
    }

    /**
     * @param  $jsonMessage
     * 给房间外所有的游客广播
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2019/4/1 12:01
     */
    static function sendOuterTouristMessage($jsonMessage)
    {
        $fds = Yii::$app->redisLocal->smembers(IMRedisKey::OUTER_TOURIST_SET);

        foreach ($fds as $fd) {
            if (!self::$iMServer->getWebSocketServer()->exist($fd)) {
                echo 'tourist fd not exist' . $fd . "\n";
                Yii::info('tourist fd not exist' . $fd);
                UserCenter::clear($fd);
                continue;
            } else {
                self::$iMServer->getWebSocketServer()->push($fd, $jsonMessage);
            }
        }
    }

    static function sendOuterLoginerMessage($jsonMessage)
    {
        $fds = Yii::$app->redisLocal->smemebers(IMRedisKey::OUTER_USER_SET);
        foreach ($fds as $fd) {
            if (!self::$iMServer->getWebSocketServer()->exist($fd)) {
                Yii::info('tourist fd not exist' . $fd);
                UserCenter::clear($fd);
                continue;
            } else {
                self::$iMServer->getWebSocketServer()->push($fd, $jsonMessage);

            }
        }
    }
}
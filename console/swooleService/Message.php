<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/18
 * Time: 11:51
 */

namespace console\swooleService;


use console\libs\Tools;
use console\swooleService\IM\IMServer;
use console\swooleService\IM\libs\IMRedisKey;
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
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2019/3/18 11:56
     */
    static function sendSingleMessage($toFd, $message,$isDelayClose=false)
    {
        if (!self::$iMServer->getWebSocketServer()->exist($toFd)) {
            return false;
        }
        self::$iMServer->getWebSocketServer()->push($toFd, Tools::wsOutput($message));
        if($isDelayClose){

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
    static function sendRoomMessage($roomId, $message)
    {
        $jsonMessage = Tools::wsOutput($message);
        $key = IMRedisKey::roomMemberKey($roomId);
        $roomMember = Yii::$app->redisLocal->HVALS($key);
        foreach ($roomMember as $v) {
            $arr = json_decode($v);
            $fd = $v['fd'];
            if (!self::$iMServer->getWebSocketServer()->exist($v['fd'])) {
                Yii::info('fd not exist' . $v);
                continue;
            } else {
                self::$iMServer->getWebSocketServer()->push($fd, $jsonMessage);

            }
        }
    }

    static function sendRoomTouristMessage($roomId, $message)
    {
        $jsonMessage = Tools::wsOutput($message);
        $touristFds = Yii::$app->redisShare->smemebers(IMRedisKey::getRoomTouristKey($roomId));
        foreach ($touristFds as $fd) {
            if (self::$iMServer->getWebSocketServer()->exist($fd)) {
                Yii::info('tourist fd not exist' . $fd);
                continue;
            } else {
                self::$iMServer->getWebSocketServer()->push($fd, $jsonMessage);

            }
        }

    }
}
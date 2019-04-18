<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/2
 * Time: 18:16
 */

namespace console\controllers\service;

use console\swooleService\IM\libs\Cmd;
use console\swooleService\IM\IMErrors;
use console\swooleService\Message;
use console\swooleService\IM\IMResponse;
use console\swooleService\IM\libs\UserCenter;
use Yii;

class ChatController extends BaseController
{
    /**
     * 房间内消息
     * @param $jsonData
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2019/4/15 18:57
     */
    function actionOnSendMsg($jsonData)
    {
        $data = json_decode($jsonData, true);
        $fromFd = $data['fromFd'];
        $jsonMsg = IMResponse::wsOutput(Cmd::D_ROOM_MSG, IMErrors::OK, 'ok', ['msg' => $data['data']['msg']]);
        $roomId = UserCenter::getRoomId($fromFd);
        echo "from fd is $fromFd,room id is $roomId \n";
        if ($roomId) {
            Message::sendMessageRoomLoginer($roomId, $jsonMsg);
            Message::sendMessageRoomTourist($roomId, $jsonMsg);
            Message::$iMServer->getMQProducer()->roomBroadcast($roomId,json_decode($jsonMsg,true));
        } else {
            Message::sendMessageOuterLoginer($jsonMsg);
            Message::sendMessageOuterTourist($jsonMsg);
        }
    }

}
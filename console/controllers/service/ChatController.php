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
        function actionOnSendMsg($jsonData){
            $data=json_decode($jsonData,true);
            $fromFd=$data['fromFd'];
            $jsonMsg=IMResponse::wsOutput(Cmd::D_ROOM_MSG,IMErrors::OK,'ok',['msg'=>$data['data']['msg']]);
            $roomId=UserCenter::getRoomId($fromFd);
            Message::sendRoomMessage($roomId,$jsonMsg);
            Message::sendRoomTouristMessage($roomId,$jsonMsg);
        }
}
<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/8
 * Time: 16:45
 */

namespace console\models\bll;

use Yii;
use yii\base\Model;

class UserBll extends Model
{
    static function connectCanPass($fd, $sid, $uid, $rid)
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
}
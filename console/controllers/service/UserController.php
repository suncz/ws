<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/11
 * Time: 10:57
 */

namespace console\controllers\service;

use console\models\RedisKey;
use Yii;
use yii\console\Controller;

class UserController extends Controller
{


    //游客
    function actionOuterTourist()
    {
        echo 111;
//        Yii::$app->runAction('/service/test/t',[]);
        Yii::$app->redisLocal->sadd(RedisKey::OUT_TOURIST_SET,111);
    }
}

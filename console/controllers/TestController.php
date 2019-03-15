<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/14
 * Time: 16:39
 */
namespace console\controllers;
use yii\console\Controller;
use Yii;
use console\models\RedisKey;
class TestController extends Controller
{
    public function actionT(){

        $userInfo['sid']=11;
        $userInfo['uid']=111;
        $userInfo['nickname']=111;
        $userInfo['headPic']=111;
        $userInfo['serverIp']=11;
        $userInfo['fd']=11;
        Yii::$app->redisLocal->hset(RedisKey::ROOM_MEMBER_HASH,11,$userInfo);
        $a =new A();
        $a->t();
        echo A::$ac;
    }
}

class A{
    static public $ac;
    function t(){
        self::$ac=222;
    }
}
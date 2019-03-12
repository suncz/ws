<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/14
 * Time: 16:39
 */
namespace console\controllers;
use yii\console\Controller;

class TestController extends Controller
{
    public function actionT(){
        $fd=123;
        Yii::$app->runAction('service/user/outerTourist',[$fd]);
    }
}
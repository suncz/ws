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
class TestController extends Controller
{
    public function actionT(){
        Yii::$app->runAction('user/outer-tourist');
    }
}
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
class Test1Controller extends Controller
{
    public function actionT(){
    }
    public function actionA(){

      var_dump(Yii::$app->runAction('test1/t'));

    }

    static function  ccc(){
        return false;
    }
}

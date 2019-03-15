<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/15
 * Time: 16:03
 */

namespace console\controllers;
use yii\console\Controller;
use Yii;
class TempController extends Controller{

    function actionSerUserToken(){
        Yii::$app->session->open();
        $sid= Yii::$app->session->getId();
        Yii::$app->session->setId($sid);
        Yii::$app->session->set('uid',10000);

    }
    function actionConfig(){

      var_dump(Yii::$app->params['adminEmail']);
    }
}
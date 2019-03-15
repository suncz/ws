<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/14
 * Time: 16:39
 */
namespace console\controllers;
use yii\console\Controller;

class Test1Controller extends Controller
{
    public function actionT($data){
        var_dump($data);
       return 124;
    }
}
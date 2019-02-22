<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/2/14
 * Time: 16:39
 */
namespace console\controllers\service;
use yii\console\Controller;
class TestController extends Controller
{
    public function actionT(){
        swoole_set_process_name('php '.' ws service/test');
        while(1){
            sleep(20);
//            echo time()."\n";
        }
    }
}
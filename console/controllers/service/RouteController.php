<?php
/**
 * Created by PhpStorm.
 * User: intro
 * Date: 2019-03-15
 * Time: 14:56
 */

namespace console\controllers\service;


class RouteController extends BaseController
{

   public $cmd;
   public $data;

   public function Index($data){
       $this->runAction($data);
   }

}
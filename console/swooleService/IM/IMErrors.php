<?php
/**
 * Created by PhpStorm.
 * User: intro
 * Date: 2019-03-15
 * Time: 15:11
 */

namespace console\swooleService\IM;


use console\swooleService\Errors;

class IMErrors extends Errors
{
    static private $errInfo=[
        self::SYSTEM_ERROR=>"系统错误",
        self::ILLEGAL_REQUEST=>"请求非法"
    ];
   static public function getErrorInfo($code){
       return self::$errInfo[$code]??"未知错误";
   }

   static public function checkParams($data){

       return true;
   }
}
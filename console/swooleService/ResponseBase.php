<?php
/**
 * Created by PhpStorm.
 * User: intro
 * Date: 2019-03-15
 * Time: 15:02
 */

namespace console\swooleService;


abstract class ResponseBase
{
    /**
     * 输出数据格式化
     * @param $format
     */
   abstract public static function wsOutput($cmd, $code, $msg = null, $data = null);
}
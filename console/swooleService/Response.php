<?php
/**
 * Created by PhpStorm.
 * User: intro
 * Date: 2019-03-15
 * Time: 15:02
 */

namespace console\swooleService;


abstract class Response
{
    /**
     * 输出数据格式化
     * @param $format
     */
   abstract public function Format($format);
}
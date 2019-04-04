<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/26
 * Time: 14:31
 */

namespace console\swooleService;


abstract class RequestBase
{
   abstract public static function input($data);
}
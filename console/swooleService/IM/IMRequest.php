<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/26
 * Time: 14:33
 */

namespace console\swooleService\IM;


use console\swooleService\RequestBase;
use Exception;

class IMRequest extends RequestBase
{
    public static function input($data)
    {
        // TODO: Implement input() method.
        $arrData=json_decode($data);
        if(!isset($arrData->cmd) || !isset($arrData->d)){
            throw new Exception(IMErrors::$msg[IMErrors::ILLEGAL_REQUEST],IMErrors::ILLEGAL_REQUEST);
        }
        return $arrData;
    }
}
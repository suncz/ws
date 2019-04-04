<?php
/**
 * Created by PhpStorm.
 * User: intro
 * Date: 2019-03-15
 * Time: 15:11
 */

namespace console\swooleService\IM;


use console\swooleService\ErrorsBase;

class IMErrors extends ErrorsBase
{

    const OK = 0;
    const CONNECT_FAIL = -1000;
    const CONNECT_OTHER = -1001;
    public static $msg = [
        self::OK => 'ok',
        self::ILLEGAL_REQUEST=>'请求错误',
        self::SYSTEM_ERROR=>'系统错误',
        self::CONNECT_FAIL => '连接失败',
        self::CONNECT_OTHER => '其他地方登陆'
    ];

}
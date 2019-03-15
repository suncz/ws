<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/14
 * Time: 17:56
 */

namespace console\models\common;


class Code
{
    const CONNECT_FAIL = -1000;
    const CONNECT_OTHER = -1001;
    static $msg = [
        Code::CONNECT_FAIL => '连接失败',
        Code::CONNECT_OTHER=>'其他地方登陆'
    ];
}

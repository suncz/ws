<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/14
 * Time: 17:42
 */

namespace console\libs;

use console\models\common\Code;

class Tools
{
    static function wsOutput($cmd, $code, $msg = null, $data = null)
    {
        $output['cmd'] = $cmd;
        $output['code'] = $code;
        if ($msg) {
            $output['msg'] = $msg;
        } else if (isset(Code::$msg[$code])) {
            $output['msg'] = Code::$msg[$code];
        }
        $data && $output['data'] = $data;
        return json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
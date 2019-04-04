<?php
/**
 * Created by PhpStorm.
 * User: intro
 * Date: 2019-03-15
 * Time: 15:06
 */

namespace console\swooleService\IM;


use console\swooleService\ResponseBase;

class IMResponse extends ResponseBase
{

    public static function wsOutput($cmd, $code, $msg = null, $data = null)
    {
        $output['cmd'] = $cmd;
        $output['code'] = $code;
        if ($msg) {
            $output['msg'] = $msg;
        } else if (isset(IMErrors::$msg[$code])) {
            $output['msg'] = IMErrors::$msg[$code];
        }
        $data && $output['data'] = $data;
        return json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * controller action 的输出格式
     * @param $code
     * @param $data
     * @return \stdClass
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2019/4/2 10:33
     */
    public static function actOutPut($code,$data){
        $std=new \stdClass();
        $std->code=$code;
        $std->data=$data;
        return $std;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: intro
 * Date: 2019-03-15
 * Time: 14:58
 */

namespace console\swooleService;

use console\swooleService\IM\IMErrors;
use function is_numeric;
use function json_encode;
use yii\db\Exception;

class MyTools
{

    static private $code= [
        Errors::ILLEGAL_REQUEST,Errors::SYSTEM_ERROR,
    ];

    /** 输出到客户端的消息体 {"cmd":"","code":"","data":""}
     * @param $cmd
     * @param $code
     * @param string $data
     * @return false|string
     */

    static public function response($cmd,$code,$data="")
    {
        $re=["cmd"=>$cmd,"code"=>0,'msg'=>'',"data"=>$data];

        if($code!=0){
           $re['code']=$code;
           $re['msg']=IMErrors::getErrorInfo($code);
        }
        return json_encode($re);

    }

    /** 通信格式判断
     * @param $data
     * @return bool|false|string
     * @throws Exception
     */
    static public function checkProtocol($data){
        $dataJson=json_decode($data,1);
        //非json格式
        if(!$dataJson){
            throw new Exception(IMErrors::getErrorInfo(IMErrors::ILLEGAL_REQUEST) ,"格式错误",IMErrors::ILLEGAL_REQUEST);
        }
        //格式错误
        if(!isset($dataJson['cmd'])||!isset($dataJson['data'])){
            throw new Exception(IMErrors::getErrorInfo(IMErrors::ILLEGAL_REQUEST) ,"参数缺失",IMErrors::ILLEGAL_REQUEST);
        }
        return $dataJson;
    }
}
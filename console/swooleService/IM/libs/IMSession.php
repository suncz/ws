<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/11
 * Time: 17:57
 */

namespace console\swooleService\IM\libs;


class IMSession
{
    public static function sessionInfo($sid)
    {
            Yii::$app->redis->get($sid);
    }
}
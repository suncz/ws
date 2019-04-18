<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/12
 * Time: 16:05
 */

namespace console\swooleService\IM\components;

use yii\redis\Connection;

class RedisHelper extends Connection
{

    function hset($key, $filed, $value)
    {
        $command[] = $key;
        $command[] = $filed;
        $command[] = $value;
        return $this->executeCommand('hset', $command);
    }

    function hmset($key, $data)
    {
        $command[]=$key;
        foreach ($data as $key => $v){
            $command[]=$key;
            $command[]=$v;
        }
        return $this->executeCommand('hmset', $command);
    }


    function hgetall($key)
    {
        $info = $this->executeCommand('hgetall', [$key]);
        if (empty($info)) {
            return [];
        }
        $data = [];
        foreach ($info as $key => $v) {
            if ($key % 2 == 0) {
                $data[$v] = $info[$key + 1];
            }
        }
        return $data;
    }

    function hmget($key, $fields)
    {
        $command = $fields;
        array_unshift($command, $key);

        $info = $this->executeCommand('hmget', $command);;

        if (empty($info)) {
            return [];
        }
        $data = [];
        foreach ($fields as $key => $v) {
            $data[$v] = $info[$key];
        }
        return $data;
    }
}
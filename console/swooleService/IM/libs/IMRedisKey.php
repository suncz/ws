<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/12
 * Time: 15:23
 */

//namespace console\swooleService\IM\libs;
namespace  console\swooleService\IM\libs;
class IMRedisKey
{
    /**********************local redis***********************/
    const ROOM_MEMBER_HASH = 'room:member:{rid}';//有登录态的房间用户
    const OUTER_USER_SET = 'outer:user';   //有登录态的房间用户

    const ROOM_TOURIST_SET = 'room:tourist:{rid}';//房间游客
    const OUT_TOURIST_SET = 'room:tourist';//房间外游客
    const ROOM_MAP_HASH='room:map';//fd 和房间id 映射

    /******************global redis*********************/
    const ROOM_ONLINE_USER_ZSET = 'room:online:user';//房间在线用户
    const USER_HASH = 'user:{uid}';//有登录态的在线用户信息
    const ROOM_MESSAGE_LIST = 'room:msg:{roomId}';

    static function roomMemberKey($rid){
        return str_replace('{rid}', $rid, self::ROOM_MEMBER_HASH);
    }

    static function getRoomTouristKey($rid)
    {
        return str_replace('{rid}', $rid, self::ROOM_TOURIST_SET);
    }

    static function getUserHashKey($userId){
        return str_replace('{uid}', $userId, self::USER_HASH);
    }
}
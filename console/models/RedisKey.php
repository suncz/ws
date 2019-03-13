<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/12
 * Time: 15:23
 */

namespace console\models;
class RedisKey
{
    /**********************local redis***********************/
    const ROOM_MEMBER_HASH = 'room:member';//有登录态的房间用户
    const ROOM_TOURIST_SET = 'room:tourist';//房间游客
    const OUT_TOURIST_SET = 'room:tourist';//房间外游客

    /******************global redis*********************/
    const ROOM_ONLINE_USER_ZSET='room:online:user';//房间在线用户
    const USER_UID_HASH='user:{uid}';//有登录态的在线用户信息
    const ROOM_MESSAGE_LIST='room:msg:{roomId}';

}
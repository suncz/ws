<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/14
 * Time: 17:51
 */

//namespace console\swooleService\IM\libs;
namespace  console\swooleService\IM\libs;

/**
 * Class Cmd
 * @package console\swooleService\IM\libs
 * 下发命令必须以d字母开头
 */
class Cmd
{
    //下发命令
    const D_CONNET='onDConnet';
    const D_REQUEST_FAIL='onDRequestFail';
    const D_SYS_FAIL='onDSysFail';
    const D_LOGIN='onDLogin';
    const D_OUT_TOURIST='onDOutTourist';
    const D_ROOM_TOURIST='onDRoomTourist';
    const D_ROOM_MSG='onDRoomMSg';

}
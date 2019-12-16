<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/11/21
 * Time: 14:40
 */

namespace app\common\util;


class ErrMsg
{
    public static function GetMsgByCode($code,$extra=null){
        $msg = "";
        switch($code){
            case 10000:$msg="用户不存在或密码错误";break;
            case 10001:$msg="用户被禁用";break;
            case 20001:$msg="请求参数有误";break;
            case 30001:$msg="数据库查询错误";break;
            case 30002:$msg="数据库插入错误";break;
            case 401:$msg = "接口未授权";break;
        }
        $msg = isset($extra)?$msg."：".$extra:$msg;
        return $msg;
    }
}
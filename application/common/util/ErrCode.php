<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/11/21
 * Time: 14:37
 */

namespace app\common\util;


class ErrCode
{

    const API_UNAUTH_ERROR = 401;

    //账号
    const USER_LOGIN_ERROR = 10000;
    const USER_DISABLE_ERROR = 10001;

    //网络请求
    const REQUEST_PARAM_ERROR = 20001;

    //数据库
    const DB_QUERY_ERROR = 30001;
    const DB_INSERT_ERROR = 30002;
}
<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/11/21
 * Time: 14:57
 */

namespace app\common\util;


use think\Db;

class RequestParam
{
    public static function GetControllerParam($model){
        $sql = "desc ".DbHelper::$db."_".strtolower($model);
        $fields = DB::query($sql);
        return $fields;
    }
}
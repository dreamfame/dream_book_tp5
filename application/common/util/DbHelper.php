<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/11/21
 * Time: 14:59
 */

namespace app\common\util;


use think\Db;
use think\Log;

class DbHelper
{
    private $table = null;
    private $sql = null;
    private $data = null;
    private $condition = null;
    private $order = null;

    public static $db = "rest";

    public function __construct()
    {
    }

    /**
     * @param $table
     * @param $operate
     * @param null $data
     * @param null $sql
     * @param null $condition
     * @param null $order
     * @return false|int|mixed|\PDOStatement|string|\think\Collection|true
     */
    public function exec($table, $operate, $data=null, $sql=null, $condition=null, $order=null){
        Db::startTrans();
        $this->table = $table;
        $this->sql = $sql;
        $this->data = $data;
        $this->condition = $condition;
        $this->order = $order;
        $result = '';
        try{
            switch($operate){
                case "create":
                    $result = $this->create();
                    break;
                case "read":
                    $result = $this->read();
                    break;
                case "update":
                    $result = $this->update();
                    break;
                case "delete":
                    $result = $this->delete();
                    break;
                case "raise":
                    $result = $this->raise();
                    break;
                case "reduce":
                    $result = $this->reduce();
                    break;
            }
            Db::commit();
        }
        catch(\Exception $e)
        {
            Log::record('数据库异常：'.$e);
            return -1;
            Db::rollback();
        }
        return $result;
    }

    //数据库新增操作
    public function create()
    {
        if($this->sql!=null){
            $result = Db::execute($this->sql);
        }
        else{
            $result = db($this->table)->insert($this->data);
        }
        return $result;
    }

    //数据库读取操作
    public function read()
    {
        if($this->sql!=null){
            $result = Db::query($this->sql);
        }
        else{
            $result = db($this->table)->where($this->condition)->order($this->order)->select();
        }
        return $result;
    }

    //数据库更新操作
    public function update()
    {
        if($this->sql!=null){
            $result = Db::query($this->sql);
        }
        else{
            $result = db($this->table)->where($this->condition)->update($this->data);
        }
        return $result;
    }

    //数据字段自增
    public function raise(){
        if($this->sql!=null){
            $result = Db::query($this->sql);
        }
        else{
            $result = db($this->table)->where($this->condition)->setInc($this->data);
        }
        return $result;
    }

    public function reduce(){
        if($this->sql!=null){
            $result = Db::query($this->sql);
        }
        else{
            $result = db($this->table)->where($this->condition)->setDec($this->data);
        }
        return $result;
    }

    //数据库删除操作
    public function delete()
    {
        if($this->sql!=null){
            $result = Db::query($this->sql);
        }
        else{
            $result = db($this->table)->where($this->condition)->delete();
        }
        return $result;
    }
}
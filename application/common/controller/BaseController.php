<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/11/19
 * Time: 16:35
 */

namespace app\common\controller;


use app\common\inter\IBase;
use app\common\util\DbHelper;
use app\common\util\ErrCode;
use app\common\util\ErrMsg;
use app\common\util\HttpAuth;
use think\Cache;
use think\controller\Rest;
use think\Request;

class BaseController extends Rest implements IBase
{
    protected static $dbHelper;

    //返回信息变量
    protected static $ret_code = 0;
    protected static $ret_msg = "";
    protected static $ret_data = "";
    protected static $data_count = 0;
    private $version = "V1";
    private $request;

    public function _initialize()
    {
        self::$dbHelper = new DbHelper();
    }

    public function __construct()
    {
        $this->request = Request::instance();
        if($this->request->controller()=="V1.Book"){
            $this->_initialize();
        }
        else{
            $result = $this->auth($this->request);
            if(!$result){
                self::$ret_code = ErrCode::API_UNAUTH_ERROR;
                self::$ret_msg = ErrMsg::GetMsgByCode(ErrCode::API_UNAUTH_ERROR);
                self::ReturnDataHandler();
            }
            else{
                $this->_initialize();
            }
        }
    }

    public function auth($request){
        $token = $request->header("Authorization");
        $cookies = $request->header("cookie");
        $cookie = explode(";",$cookies);
        foreach($cookie as $k=>$v){
            $v = explode("=",$v);
            if($v[0]=="sessionId"){
                $sessionId = $v[1];
            }
        }
        if(isset($sessionId)){
            $user_info =json_decode(Cache::get($sessionId),JSON_OBJECT_AS_ARRAY);
            $secret = $user_info['secret'];
            $auth = new HttpAuth();
            $auth_result = $auth->JWT_VerifyToken($token,$secret);
            if($auth_result){
                return true;
            }
            else{
               return false;
            }
        }
        else{
            return false;
        }
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function read()
    {
        $model = strtolower(str_replace($this->version.".","",$this->request->controller()));
        $id = $this->request->route()['id'];
        if($id=="list"){
            $condition = null;
        }
        else{
            $condition['id'] = $id;
        }
        $db_result = self::$dbHelper->exec($model,"read",null,null,$condition);
        if($db_result!==-1){
            self::$ret_msg = "查询成功";
            self::$ret_data = $db_result;
        }
        else{
            self::$ret_code = ErrCode::DB_QUERY_ERROR;
            self::$ret_msg = ErrMsg::GetMsgByCode(ErrCode::DB_QUERY_ERROR);
        }
        self::ReturnDataHandler();
    }

    public function update()
    {
        // TODO: Implement update() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    protected static function ReturnDataHandler(){
        $retObj = array('code'=>self::$ret_code,'msg'=>self::$ret_msg,'data'=>self::$ret_data);
        $jsonStr = json_encode($retObj,JSON_UNESCAPED_UNICODE);
        echo $jsonStr;
        exit(0);
    }

    /**
     * 图片上传返回数据处理
     */
    protected static function ImageReturnDataHandler(){
        $retObj = array('code'=>self::$ret_code,'msg'=>self::$ret_msg,'data'=>self::$ret_data);
        $jsonStr = json_encode($retObj,JSON_UNESCAPED_UNICODE);
        echo $jsonStr;
        exit(0);
    }
}
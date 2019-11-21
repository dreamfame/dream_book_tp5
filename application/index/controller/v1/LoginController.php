<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/11/20
 * Time: 14:52
 */

namespace app\index\controller\v1;


use app\common\controller\BaseController;
use app\common\util\DbHelper;
use app\common\util\ErrCode;
use app\common\util\ErrMsg;
use app\common\util\HttpAuth;
use think\Cache;
use think\exception\HttpException;
use think\Request;
use think\Validate;

class LoginController extends BaseController
{
    private $request = null;
    private $validate;

    public function __construct()
    {
        $this->request = Request::instance();
        self::$dbHelper = new DbHelper();
        $rule = [
            'username' => 'require',
            'password' => 'require',
        ];

        $msg = [
            'username.require' => '账号不可为空',
            'password.require' => '密码不可为空'
        ];
        $this->validate = new Validate($rule, $msg);
    }

    public function login()
    {
        $param = input('get.');
        $this->validate->scene("login", ["username", "password"]);
        $check_result = $this->validate->scene("login")->check($param);
        if ($check_result) {
            try {
                $condition = array();
                $condition['username|phone|email'] = $param['username'];
                $condition['password'] = $param['password'];
                $result = self::$dbHelper->exec("user", "read", null, null,$condition);
                if ($result == -1) {
                    self::$ret_code = ErrCode::USER_LOGIN_ERROR;
                    self::$ret_msg = ErrMsg::GetMsgByCode(ErrCode::USER_LOGIN_ERROR);
                } else {
                    $user = $result[0];
                    if ($user['status'] == 1) {
                        self::$ret_msg = "登录成功";
                        $payload=array('iss'=>'liuliu','iat'=>time(),'exp'=>time()+7200,'nbf'=>time(),'sub'=>$user['username'],'jti'=>md5(uniqid('JWT').time()));
                        $token = HttpAuth::JWT_GenerateToken($payload,$user['secret']);
                        session_start();
                        $data = array(
                            "token"=>$token,
                            "sessionId"=>session_id(),
                            "expireTime"=>date( "Y-m-d H:i:s", time()+7200),
                            "user"=>$user['username'],
                            "role"=>$user["role"]
                        );
                        $user_info = json_encode(array("username"=>$param['username'],"password"=>$param['password'],"role"=>$data['role'],'secret'=>$user['secret']), JSON_UNESCAPED_UNICODE);
                        Cache::set($data['sessionId'],$user_info,7200);
                        self::$ret_data = $data;
                    } else {
                        self::$ret_code = ErrCode::USER_DISABLE_ERROR;
                        self::$ret_msg = ErrMsg::GetMsgByCode(ErrCode::USER_DISABLE_ERROR);
                    }
                }
            } catch (HttpException $e) {
                echo $e->getCode();
            }
        } else {
            self::$ret_code = ErrCode::REQUEST_PARAM_ERROR;
            self::$ret_msg = ErrMsg::GetMsgByCode(ErrCode::REQUEST_PARAM_ERROR, $this->validate->getError());
        }
        self::ReturnDataHandler();
    }

    public function logout()
    {

    }
}
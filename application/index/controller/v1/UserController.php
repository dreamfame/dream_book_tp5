<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/11/21
 * Time: 17:20
 */

namespace app\index\controller\v1;


use app\common\controller\BaseController;
use app\common\util\DbHelper;
use think\Request;
use think\Validate;

class UserController extends BaseController
{
    private $request;
    private $validate;

    public function _initialize()
    {
        parent::_initialize();
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


    public function read()
    {
        parent::read();
    }
}
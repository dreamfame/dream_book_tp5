<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/12/16
 * Time: 16:14
 */

namespace app\index\controller\v1;


use app\common\controller\BaseController;
use app\common\util\DbHelper;
use app\common\util\ErrCode;
use app\common\util\ErrMsg;
use app\common\util\HttpUtils;
use think\Request;
use think\Validate;

class BookController extends BaseController
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

    public function create(){
        parent::create();
        $param = input('post.');
        $isbn = $param['isbn'];
        $url = "https://api.douban.com/v2/book/isbn/".$isbn;
        $data = "apikey=0b2bdeda43b5688921839c8ecb20399b";
        $header = array('Content-Type: application/x-www-form-urlencoded');
        $book_data = HttpUtils::HttpPost($url,$data,$header);
        $insert_data = [
          "name" => $book_data['title'],
          "price" => $book_data['price'],
          "num"=>1
        ];
        $result = self::$dbHelper->exec("book", "create", $insert_data);
        if ($result == -1) {
            self::$ret_code = ErrCode::DB_INSERT_ERROR;
            self::$ret_msg = ErrMsg::GetMsgByCode(ErrCode::DB_INSERT_ERROR);
        }
        else{
            self::$ret_msg = "录入成功";
        }
        self::ReturnDataHandler();

    }
}
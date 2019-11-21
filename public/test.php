<?php
$realm = 'Restricted area';

$username = 'ser';          //帐号
$passowrd = '666666';       //密码

if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorization');    //此头弹出登录窗口
    header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth", nonce="'.uniqid().'", opaque="'.md5($realm).'"');
    die('您取消了本次登录，若重新登录，请刷新此页面。');
}else{

    //使用函数http_digest_parse解析验证信息
    if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || $data['username']!=$username){
        header("HTTP/1.1 401 Unauthorization Required");
        header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth", nonce="'.uniqid().'", opaque="'.md5($realm).'"');//IE 8 需要重新发送，不然不弹窗
        die('账号错误！');
    }

    //拼接字符串
    $A1 = md5($username . ':' . $realm . ':' . $passowrd);
    $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
    $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

    if ($data['response'] != $valid_response){
        header("HTTP/1.1 401 Unauthorization Required");
        header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth", nonce="'.uniqid().'", opaque="'.md5($realm).'"');
        die('账号/密码错误！');
    }

    echo 'Hi '.$username.',恭喜你登录成功！';
}

// 解析字符串方法
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();

    preg_match_all('@(\w+)=([\'"]?)([a-zA-Z0-9=./\_-]+)\2@', $txt, $matches, PREG_SET_ORDER);
    //print_r($matches);
    foreach ($matches as $m) {
        $data[$m[1]] = $m[3];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}
?>
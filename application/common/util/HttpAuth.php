<?php
/**
 * Created by PhpStorm.
 * User: liuliu
 * Date: 2019/11/20
 * Time: 11:30
 */

namespace app\common\util;


class HttpAuth
{
    private $realm = "dreamfame";

    private static $jwt_header=array(
        'alg'=>'HS256', //生成signature的算法
        'typ'=>'JWT'    //类型
    );

    /***
     * jwt生成token
     * @param $payload 数据体
     * @param string $secret 密钥
     * @return bool|string 返回token
     */
    public static function JWT_GenerateToken($payload,$secret="666666"){
        if(is_array($payload)) {
            $base64Header = self::base64UrlEncode(json_encode(self::$jwt_header, JSON_UNESCAPED_UNICODE));
            $base64Payload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
            $token = $base64Header . '.' . $base64Payload . '.' . self::signature($base64Header . '.' . $base64Payload, $secret, self::$jwt_header['alg']);
            return $token;
        }
        else{
            return false;
        }
    }

    /***
     * jwt验证token
     * @param $token 客户端token
     * @param $secret 密钥
     * @return bool|mixed
     */
    public function JWT_VerifyToken($token,$secret="666666"){
        $tokens = explode('.', $token);
        if (count($tokens) != 3)
            return false;

        list($base64Header, $base64Payload, $sign) = $tokens;

        //获取jwt算法
        $base64DecodeHeader = json_decode(self::base64UrlDecode($base64Header), JSON_OBJECT_AS_ARRAY);
        if (empty($base64DecodeHeader['alg']))
            return false;

        //签名验证
        if (self::signature($base64Header . '.' . $base64Payload, $secret, $base64DecodeHeader['alg']) !== $sign)
            return false;

        $payload = json_decode(self::base64UrlDecode($base64Payload), JSON_OBJECT_AS_ARRAY);

        //签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > time())
            return false;

        //过期时间小宇当前服务器时间验证失败
        if (isset($payload['exp']) && $payload['exp'] < time())
            return false;

        //该nbf时间之前不接收处理该Token
        if (isset($payload['nbf']) && $payload['nbf'] > time())
            return false;

        return $payload;
    }

    /**
     * base64UrlEncode   https://jwt.io/  中base64UrlEncode编码实现
     * @param string $input 需要编码的字符串
     * @return string
     */
    private static function base64UrlEncode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64UrlEncode  https://jwt.io/  中base64UrlEncode解码实现
     * @param string $input 需要解码的字符串
     * @return bool|string
     */
    private static function base64UrlDecode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * HMACSHA256签名   https://jwt.io/  中HMACSHA256签名实现
     * @param string $input 为base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param string $key
     * @param string $alg 算法方式
     * @return mixed
     */
    private static function signature($input, $key, $alg = 'HS256')
    {
        $alg_config=array(
            'HS256'=>'sha256'
        );
        return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key,true));
    }

    public function HttpBasic(){

    }

    /**
     * @param $username 用户名
     * @param $password 密码
     * @return int 0：取消验证 1：验证通过 -1：身份验证失败
     */
    public function HttpDigest($username,$password){
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/1.1 401 Unauthorization Required');    //此头弹出登录窗口
            header('WWW-Authenticate: Digest realm="'.$this->realm.'",qop="auth", nonce="'.uniqid().'", opaque="'.md5($this->realm).'"');
            return 0;
        }else{
            //使用函数http_digest_parse解析验证信息
            if (!($data = $this->http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || $data['username']!=$username){
                header("HTTP/1.1 401 Unauthorization Required");
                header('WWW-Authenticate: Digest realm="'.$this->realm.'",qop="auth", nonce="'.uniqid().'", opaque="'.md5($this->realm).'"');//IE 8 需要重新发送，不然不弹窗
               return -1;
            }
            //拼接字符串
            $A1 = md5($username . ':' . $this->realm . ':' . $password);
            $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
            $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
            if ($data['response'] != $valid_response){
                header("HTTP/1.1 401 Unauthorization Required");
                header('WWW-Authenticate: Digest realm="'.$this->realm.'",qop="auth", nonce="'.uniqid().'", opaque="'.md5($this->realm).'"');
                return -1;
            }
            return 1;
        }
    }

    public function http_digest_parse($txt)
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
}
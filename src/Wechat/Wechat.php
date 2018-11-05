<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/3 15:02
 */


namespace luweiss\Wechat;


use luweiss\Curl\Curl;

/**
 * Class Wechat
 * @package luweiss\Wechat
 * @property Curl $curl
 */
class Wechat extends WechatBase
{
    public $appId;
    public $appSecret;

    private $accessToken;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * @param array $result
     * @return array
     * @throws WechatException
     */
    public function getClientResult($result)
    {
        if (isset($result['errcode']) && $result['errcode'] !== 0) {
            $msg = 'errCode: ' . $result['errcode'] . ', errMsg: ' . $result['errmsg'];
            throw new WechatException($msg);
        }
        return $result;
    }

    /**
     * @return string
     * @throws WechatException
     */
    public function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        $api = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='
            . $this->appId
            . '&secret=' . $this->appSecret;
        $res = $this->getClient()->get($api);
        $this->accessToken = $res['access_token'];
        return $this->accessToken;
    }
}

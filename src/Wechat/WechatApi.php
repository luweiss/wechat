<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/1/18
 * Time: 10:36
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace luweiss\Wechat;


class WechatApi
{
    private $wechat;
    public $postDataType = 'body';

    /**
     * WechatApi constructor.
     * @param Wechat $wechat
     */
    public function __construct($wechat)
    {
        $this->wechat = $wechat;
    }

    public function setPostDataType($dataType)
    {
        $this->postDataType = $dataType;
        return $this;
    }

    /**
     * @return Wechat
     */
    public function getWechat()
    {
        return $this->wechat;
    }

    /**
     * @param $api
     * @param $method
     * @param $data
     * @return array
     * @throws WechatException
     */
    public function api($api, $method, $data)
    {
        $wechat = $this->getWechat();
        switch ($method) {
            case 'get':
                $res = $wechat->getClient()->get($api, $data);
                break;
            case 'post':
                $res = $wechat->getClient()->setPostDataType($this->postDataType)->post($api, $data);
                break;
            default:
                throw new WechatException('暂不支持' . $method . '请求');
        }
        return $this->getClientResult($res);
    }

    /**
     * @param $api
     * @param array $data
     * @param array $params
     * @return array
     * @throws WechatException
     */
    public function apiPost($api, $data = [], $params = [])
    {
        $res = $this->getWechat()->getClient()->setPostDataType($this->postDataType)->post($api, $data, $params);
        return $this->getClientResult($res);
    }

    /**
     * @param $api
     * @param array $params
     * @return array
     * @throws WechatException
     */
    public function apiGet($api, $params = [])
    {
        $res = $this->getWechat()->getClient()->post($api, $params);
        return $this->getClientResult($res);
    }

    /**
     * @param array $result
     * @return array
     * @throws WechatException
     */

    public function getClientResult($result)
    {
        if (!isset($result['errcode'])) {
            throw new WechatException(
                '返回数据格式不正确: ' . json_encode($result, JSON_UNESCAPED_UNICODE)
            );
        }
        if ($result['errcode'] !== 0) {
            $msg = 'errcode: ' . $result['errcode'] . ', errmsg: ' . $result['errmsg'];
            throw new WechatException($msg, 0, null, $result);
        }
        return $result;
    }
}
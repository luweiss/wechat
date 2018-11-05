<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/5 11:52
 */


namespace luweiss\Wechat;


class WechatPay extends WechatBase
{
    const SIGN_TYPE_MD5 = 'MD5';

    public $appId;
    public $mchId;
    public $key;
    public $certPemFile;
    public $keyPemFile;

    public function __construct($args)
    {
    }

    /**
     * @param array $result
     * @return array
     * @throws WechatException
     */
    protected function getClientResult($result)
    {
        if (!isset($result['return_code'])) {
            throw new WechatException(
                '返回数据格式不正确: ' . json_encode($result, JSON_UNESCAPED_UNICODE)
            );
        }
        if ($result['return_code'] !== 'SUCCESS') {
            $msg = 'returnCode: ' . $result['return_code'] . ', returnMsg: ' . $result['return_msg'];
            throw new WechatException($msg);
        }
        if (!isset($result['result_code'])) {
            throw new WechatException(
                '返回数据格式不正确: ' . json_encode($result, JSON_UNESCAPED_UNICODE)
            );
        }
        if ($result['result_code'] !== 'SUCCESS') {
            $msg = 'errCode: ' . $result['err_code'] . ', errCodeDes: ' . $result['err_code_des'];
            throw new WechatException($msg);
        }
        return $result;
    }

    /**
     * @param $api
     * @param $args
     * @return array
     * @throws WechatException
     */
    protected function send($api, $args)
    {
        $args['sign'] = $this->makeSign($args);
        $xml = WechatHelper::arrayToXml($args);
        $res = $this->getClient()->setDataType(WechatHttpClient::DATA_TYPE_XML)->post($api, $xml);
        return $this->getClientResult($res);
    }

    /**
     * @param $api
     * @param $args
     * @return array
     * @throws WechatException
     */
    protected function sendWithPem($api, $args)
    {
        $args['sign'] = $this->makeSign($args);
        $xml = WechatHelper::arrayToXml($args);
        $res = $this->getClient()
            ->setDataType(WechatHttpClient::DATA_TYPE_XML)
            ->setCertPemFile($this->certPemFile)
            ->setKeyPemFile($this->keyPemFile)
            ->post($api, $xml);
        return $this->getClientResult($res);
    }

    /**
     *
     * 统一下单, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function unifiedOrder($args)
    {
        $api = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        return $this->send($api, $args);
    }

    /**
     *
     * 查询订单, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_2">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_2</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function orderQuery($args)
    {
        $api = 'https://api.mch.weixin.qq.com/pay/orderquery';
        return $this->send($api, $args);
    }

    /**
     *
     * 关闭订单, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_3">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_3</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function closeOrder($args)
    {
        $api = 'https://api.mch.weixin.qq.com/pay/closeorder';
        return $this->send($api, $args);
    }

    /**
     *
     * 申请退款, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_4">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_4</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function refund($args)
    {
        $api = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
        return $this->sendWithPem($api, $args);
    }

    /**
     *
     * 查询退款, <a href="https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_5">
     * https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_5</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function refundQuery($args)
    {
        $api = 'https://api.mch.weixin.qq.com/pay/refundquery';
        return $this->send($api, $args);
    }

    /**
     *
     * 企业付款, <a href="https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2">
     * https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function transfers($args)
    {
        $api = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        return $this->sendWithPem($api, $args);
    }

    /**
     *
     * 查询企业付款, <a href="https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_3">
     * https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_3</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function getTransferInfo($args)
    {
        $api = '	https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';
        return $this->sendWithPem($api, $args);
    }

    /**
     *
     * 企业付款到银行卡, <a href="https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_2">
     * https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_2</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function payBank($args)
    {
        $api = 'https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank';
        return $this->sendWithPem($api, $args);
    }

    /**
     *
     * 查询企业付款到银行卡, <a href="https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_3">
     * https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_3</a>
     *
     * @param array $args
     * @return array
     * @throws WechatException
     */
    public function queryBank($args)
    {
        $api = 'https://api.mch.weixin.qq.com/mmpaysptrans/query_bank';
        return $this->sendWithPem($api, $args);
    }

    /**
     * 通过数组数据验证签名
     * @param array $array
     * @return bool
     */
    public function validateSignByArrayResult($array)
    {
        if (!isset($array['sign'])) {
            return false;
        }
        $inputSign = $array['sign'];
        $truthSign = $this->makeSign($array);
        return $inputSign === $truthSign;
    }

    /**
     * 通过XML数据验证签名
     * @param string $xml
     * @return bool
     */
    public function validateSignByXmlResult($xml)
    {
        $array = WechatHelper::xmlToArray($xml);
        return $this->validateSignByArrayResult($array);
    }

    /**
     * 数据签名
     * @param array $args
     * @param string $signType
     * @return string
     */
    public function makeSign($args, $signType = self::SIGN_TYPE_MD5)
    {
        if (isset($args['sign'])) {
            unset($args['sign']);
        }
        ksort($args);
        foreach ($args as $i => $arg) {
            if ($args === null || $arg === '') {
                unset($args[$i]);
            }
        }
        $string = http_build_query($args);
        $string = $string . "&key={$this->key}";
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }
}

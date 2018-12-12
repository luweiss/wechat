<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/3 15:31
 */


namespace luweiss\Wechat;


use luweiss\Curl\Curl;
use luweiss\Curl\CurlException;

/**
 * Class WechatHttpClient
 * @package luweiss\Wechat
 * @property Curl $curl
 */
class WechatHttpClient
{
    const DATA_TYPE_JSON = 'json';
    const DATA_TYPE_XML = 'xml';

    private $curl;
    public $dataType = 'json';

    public function __construct()
    {
        $this->curl = new Curl();
    }

    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
        return $this;
    }

    public function setCertPemFile($file)
    {
        $this->curl->setOption(CURLOPT_SSLCERTTYPE, 'PEM');
        $this->curl->setOption(CURLOPT_SSLCERT, $file);
        return $this;
    }

    public function setKeyPemFile($file)
    {
        $this->curl->setOption(CURLOPT_SSLCERTTYPE, 'PEM');
        $this->curl->setOption(CURLOPT_SSLKEY, $file);
        return $this;
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws WechatException
     */
    public function get($url, $params = [])
    {
        return $this->curlSend([
            'url' => $url,
            'params' => $params,
        ], 'get');
    }

    /**
     * @param $url
     * @param array $data
     * @param array $params
     * @return mixed
     * @throws WechatException
     */
    public function post($url, $data = [], $params = [])
    {
        return $this->curlSend([
            'url' => $url,
            'data' => $data,
            'params' => $params,
        ], 'post');
    }

    /**
     * @param $args
     * @param $type
     * @return mixed
     * @throws WechatException
     */
    private function curlSend($args, $type)
    {
        $errorCodes = require __DIR__ . '/errors.php';
        try {
            $curl = $this->curl;
            if ($type === 'post') {
                $response = $curl->post($args['url'], $args['data'], $args['params']);
            } else {
                $response = $curl->get($args['url'], $args['params']);
            }
            if ($response->headers['http_code'] !== 200) {
                throw new CurlException($response->headers['http_code_status']);
            }
            $body = $response->body;
            if ($this->dataType === static::DATA_TYPE_JSON) {
                $result = json_decode($body, true);
                if (!$result) {
                    throw new WechatException('微信接口返回的结果不是有效的json类型数据。Body: ' . $body);
                }
            } elseif ($this->dataType === static::DATA_TYPE_XML) {
                $result = WechatHelper::xmlToArray($body);
                if (!$result) {
                    throw new WechatException('微信接口返回的结果不是有效的xml类型数据。Body: ' . $body);
                }
            }
            if (isset($result['errcode']) && $result['errcode'] !== 0) {
                $errMsg = isset($errorCodes[$result['errcode']]) ?
                    $errorCodes[$result['errcode']]
                    : (isset($result['errmsg']) ? $result['errmsg'] : '');
                throw new WechatException('errCode ' . $result['errcode'] . ($errMsg ? (', ' . $errMsg) : ''));
            }
            return $result;
        } catch (CurlException $exception) {
            throw new WechatException($exception->getMessage(), 0, $exception);
        } catch (WechatException $exception) {
            throw $exception;
        }
    }
}

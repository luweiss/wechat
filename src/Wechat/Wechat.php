<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/3 15:02
 */


namespace luweiss\Wechat;


use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
use luweiss\Curl\Curl;

/**
 * Class Wechat
 * @package luweiss\Wechat
 * @property Curl $curl
 * @property Cache $cache
 */
class Wechat extends WechatBase
{
    const CACHE_TARGET_FILE = 'file';
    const CACHE_TARGET_REDIS = 'redis';
    const CACHE_TARGET_MEMCACHED = 'memcached';
    const CACHE_TARGET_APCU = 'apcu';
    public $appId;
    public $appSecret;

    public $cache;

    private $accessToken;

    /**
     * Wechat constructor.
     * @param array $config <br>
     * <br> [
     * <br>     'appId' => '',
     * <br>     'appSecret' => '',
     * <br>     'cache' =>
     * <br>             [
     * <br>                 'target' => Wechat::CACHE_TARGET_XXX,
     * <br>                 'dir' => '文件缓存目录',
     * <br>                 'host' => 'redis或memcached服务器',
     * <br>                 'port' => 'redis或memcached端口',
     * <br>             ],
     * <br> ]
     * @throws WechatException
     */
    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
        $this->setCacheComponent();
    }

    private function setCacheComponent()
    {
        if (!$this->cache) {
            $this->cache = new FilesystemCache(dirname(dirname(__DIR__)) . '/runtime/cache');
        }
        $target = !empty($this->cache['target']) ? $this->cache['target'] : static::CACHE_TARGET_FILE;
        switch ($target) {
            case static::CACHE_TARGET_FILE:
                $dir = !empty($this->cache['dir']) ?
                    $this->cache['dir'] : (dirname(dirname(__DIR__)) . '/runtime/cache');
                $this->cache = new FilesystemCache($dir);
                break;
            case static::CACHE_TARGET_REDIS:
                $host = !empty($this->cache['host']) ? $this->cache['host'] : '127.0.0.1';
                $port = !empty($this->cache['port']) ? $this->cache['port'] : 6379;
                $redis = new \Redis();
                $redis->connect($host, $port);
                $this->cache = new RedisCache();
                $this->cache->setRedis($redis);
                break;
            case static::CACHE_TARGET_MEMCACHED:
                $host = !empty($this->cache['host']) ? $this->cache['host'] : '127.0.0.1';
                $port = !empty($this->cache['port']) ? $this->cache['port'] : 6379;
                $memcached = new \Memcached();
                $memcached->addServer($host, $port);
                $this->cache = new MemcachedCache();
                $this->cache->setMemcached($memcached);
                break;
            case static::CACHE_TARGET_APCU:
                $this->cache = new ApcuCache();
                break;
            default:
                throw new WechatException('无效的cache target `' . $target . '`。');
                break;
        }
        return $this;
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
     * @param bool $refresh 是否刷新access token，不从缓存获取
     * @return string
     * @throws WechatException
     */
    public function getAccessToken($refresh = false)
    {
        if (!$this->appId) {
            throw  new WechatException('appId 不能为空。');
        }
        if (!$this->appSecret) {
            throw  new WechatException('appSecret 不能为空。');
        }
        if ($this->accessToken) {
            return $this->accessToken;
        }
        $cacheKey = 'ACCESS_TOKEN_OF_APPID-' . $this->appId;
        if (!$refresh) {
            $this->accessToken = $this->cache->fetch($cacheKey);
            if ($this->accessToken) {
                return $this->accessToken;
            }
        }
        $api = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='
            . $this->appId
            . '&secret=' . $this->appSecret;
        $res = $this->getClient()->get($api);
        $this->accessToken = $res['access_token'];
        $this->cache->save($cacheKey, $this->accessToken, 7000);
        return $this->accessToken;
    }
}

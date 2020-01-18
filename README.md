# PHP Wechat SDK

## Usage

### Install

```bash
composer require 'luweiss/wechat'
```

### Wechat

```php
require __DIR__ . '/vendor/autoload.php';

$wechat = new \luweiss\Wechat\Wechat();
$accessToken = $wechat->getAccessToken;
```

### WechatPay

```php
require __DIR__ . '/vendor/autoload.php';

$wechatPay = new \luweiss\Wechat\WechatPay();
$res = $wechatPay->unifiedOrder();
```

### WechatApi
```微信接口调用类```

### WechatSubscribe
```订阅消息接口```
```php
$wechat = new \luweiss\Wechat\Wechat();
$tid = 434;     // 模板标题 id，可通过接口获取，也可登录小程序后台查看获取
$kidList = [6, 5, 9, 1];      // 开发者自行组合好的模板关键词列表，可以通过 `getTemplateKeywords` 方法获取
$sceneDesc = '下单成功通知';    // 服务场景描述，非必填

$wechatSubscribe = new \luweiss\Wechat\WechatSubscribe($wechat);
$res = $wechatSubscribe->addTemplate($tid, $kidList, $sceneDesc);
```

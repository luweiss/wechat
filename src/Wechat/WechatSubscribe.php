<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/1/18
 * Time: 10:05
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace luweiss\Wechat;


class WechatSubscribe extends WechatApi
{
    public function getAccessToken($refresh = false)
    {
        return $this->getWechat()->getAccessToken($refresh);
    }

    /**
     * @param $tid integer 模板标题 id，可通过接口获取，也可登录小程序后台查看获取 例如AT0002
     * @param $kidList array 开发者自行组合好的模板关键词列表，关键词顺序可以自由搭配（例如 [3,5,4] 或 [4,5,3]），最多支持5个，最少2个关键词组合
     * @param $sceneDesc string 服务场景描述，15个字以内
     * @return array
     * @throws WechatException
     * 组合模板并添加至帐号下的个人模板库
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.addTemplate.html
     */
    public function addTemplate($tid, $kidList, $sceneDesc)
    {
        $accessToken = $this->getAccessToken();
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token={$accessToken}";
        $res = $this->setPostDataType(WechatHttpClient::POST_DATA_TYPE_FORM_PARAMS)->apiPost($api, [
            'tid' => $tid,
            'kidList' => $kidList,
            'sceneDesc' => $sceneDesc
        ]);
        return $this->getClientResult($res);
    }

    /**
     * @param $priTmplId string 要删除的模板id 例如wDYzYZVxobJivW9oMpSCpuvACOfJXQIoKUm0PY397Tc
     * @return array
     * @throws WechatException
     * 删除帐号下的某个模板
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.deleteTemplate.html
     */
    public function deleteTemplate($priTmplId)
    {
        $accessToken = $this->getAccessToken();
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate?access_token={$accessToken}";
        $res = $this->setPostDataType(WechatHttpClient::POST_DATA_TYPE_FORM_PARAMS)->apiPost($api, [
            'priTmplId' => $priTmplId,
        ]);
        return $this->getClientResult($res);
    }

    /**
     * @return array
     * @throws WechatException
     * 获取小程序账号的类目
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getCategory.html
     */
    public function getCategory()
    {
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/getcategory?access_token={$this->getAccessToken()}";
        $res = $this->apiGet($api);
        return $this->getClientResult($res);
    }

    /**
     * @param $tid string 模板标题 id，可通过接口获取
     * @return array
     * @throws WechatException
     * 获取模板标题下的关键词列表
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getPubTemplateKeyWordsById.html
     */
    public function getPubTemplateKeyWordsById($tid)
    {
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatekeywords?access_token={$this->getAccessToken()}";
        $res = $this->apiGet($api, [
            'tid' => $tid
        ]);
        return $this->getClientResult($res);
    }

    /**
     * @param $ids string 类目 id，多个用逗号隔开
     * @param $start number 用于分页，表示从 start 开始。从 0 开始计数。
     * @param $limit number 用于分页，表示拉取 limit 条记录。最大为 30。
     * @return array
     * @throws WechatException
     * 获取帐号所属类目下的公共模板标题
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getPubTemplateTitleList.html
     */
    public function getPubTemplateTitleList($ids, $start, $limit)
    {
        if ($limit >= 30) {
            $limit = 30;
        }
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatetitles?access_token={$this->getAccessToken()}";
        $res = $this->apiGet($api, [
            'ids' => $ids,
            'start' => $start,
            'limit' => $limit,
        ]);
        return $this->getClientResult($res);
    }

    /**
     * @return array
     * @throws WechatException
     * 获取当前帐号下的个人模板列表
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getTemplateList.html
     */
    public function getTemplateList()
    {
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token={$this->getAccessToken()}";
        $res = $this->apiGet($api);
        return $this->getClientResult($res);
    }

    /**
     * @param $arg array
     * @return array
     * @throws WechatException
     * 发送订阅消息
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.send.html
     */
    public function send($arg = array())
    {
        if (!isset($arg['touser']) || !$arg['touser']) {
            throw new WechatException('touser字段缺失，请填写接收者（用户）的 openid');
        }
        if (!isset($arg['template_id']) || !$arg['template_id']) {
            throw new WechatException('template_id字段缺失，请填写所需下发的模板消息的id');
        }
        $api = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$this->getAccessToken()}";
        $res = $this->setPostDataType(WechatHttpClient::POST_DATA_TYPE_BODY)->apiPost($api, json_encode($arg, JSON_UNESCAPED_UNICODE));
        return $this->getClientResult($res);
    }
}
<?php

namespace app\home\controller;
use app\utils\MailerUtil;
use think\Exception;

class Test
{
    public function index()
    {
//        var_dump(time());die;
        try {
            var_dump(time());
//            $res1 = MailerUtil::send("316770656@qq.com",'dzg',2,'test');
//            $res = MailerUtil::send("1404149445@qq.com",'dzg',2,'该类型收件人被收件人服务器判为垃圾邮件比例过高，导致平台拒绝投递该域名剩余邮件！建议调整邮件模板');
            $res = MailerUtil::send("792327182@qq.com",'dzg',2,'该类型收件人被收件人服务器判为垃圾邮件比例过高，导致平台拒绝投递该域名剩余邮件！建议调整邮件模板');
            //$res2 = MailerUtil::send("deng_z_hui@163.com",'dzg',2,'test');
            var_dump('res'.$res);
            //var_dump('res1'.$res1);
            //var_dump('res2'.$res2);
        }catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
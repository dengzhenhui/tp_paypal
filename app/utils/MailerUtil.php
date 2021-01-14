<?php


namespace app\utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * 邮件工具类
 * Class MailerUtil
 * @package app\utils
 */
class MailerUtil
{
    /**
     * @param $userEmail string 用户邮箱
     * @param $userName string 用户名
     * @param $type int 发送邮件类型 1：发票
     * @param $url string 发票的地址url
     * @return bool true 发送成功；false 发送失败
     * @throws Exception
     */
    public static function send($userEmail, $userName, $type=1, $url = '')
    {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                      // Enable verbose debug output
//        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host = 'smtp.bestedm.org';                    // Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   // Enable SMTP authentication
        $mail->Username = 'Huion@service.huion.com';                     // SMTP username
        $mail->Password = 'Huion2019it';                               // SMTP password
        //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port = 2525;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        $mail->CharSet = 'UTF-8'; //设定邮件编码

        //Recipients
        $mail->setFrom('Huion@service.huion.com', 'Huion'); // 发件人
        $mail->addAddress($userEmail, $userName);// 收件人

        $mail->isHTML(true);                                  // Set email format to HTML

        if (1 == $type) list($Subject, $Body) = self::getInvoiceText($url);
        if (2 == $type) list($Subject, $Body) = self::getQueueText($url);

        $mail->Subject = $Subject;
        $mail->Body = $Body;
        $res = $mail->send();
        return $res; // true 是发送成功；false 是发送失败
    }

    /**
     * 发票 文案
     */
    protected static function getInvoiceText($url)
    {
        $Subject = '发票';
        $Body = "亲爱的客户：<br />
        <br />
        感谢您相信绘王官方。<br />
        请点击下面的链接下载您的发票<br />
        <a href= $url style='color: #00BFD6;'>$url</a><br />
        （如果点击链接没反应，请复制当前链接，粘贴到浏览器地址栏后访问）<br />
       <br />
        <br />【HUION绘王】<br />"
            . date('Y年m月d日') .
            "<br />
        <br />
        如您错误的收到了此邮件，请不要点击链接。<br />
        【HUION绘王】账号安全服务系统邮件，请勿直接回复。<br />
        如有疑问，请联系客服中心400-001-3566";

        return [$Subject, $Body];
    }

    /**
     * 队列任务出问题 文案
     */
    protected static function getQueueText($url)
    {
        $Subject = '队列出问题了';
        $Body = $url;

        return [$Subject, $Body];
    }

}
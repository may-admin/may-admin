<?php
namespace app\service;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    protected PHPMailer $mailer;
    
    protected array $config = [
        'host'       => 'smtp.example.com', // SMTP服务器
        'port'       => 465,                // SMTP端口
        'username'   => 'test@example.com', // SMTP账号
        'password'   => 'xxxxxxxx',         // SMTP密码 / 授权码
        'from_email' => 'test@example.com', // 发件人邮箱
        'from_name'  => '我的网站',         // 发件人名称
        'encryption' => 'ssl',              // 加密方式 / ssl / tls
        'charset'    => 'UTF-8',            // 字符集
        'debug'      => false,              // 是否开启调试
    ];

    /**
     * 构造方法
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = !empty($config) ? array_merge($this->config, $config) : $this->config;
        $this->mailer = new PHPMailer(true);
        $this->init();   // 初始化
    }
    
    /**
     * 初始化 PHPMailer
     * @return void
     */
    protected function init(): void
    {
        $this->mailer->isSMTP();   // 使用 SMTP
        $this->mailer->Host = $this->config['host'];   // SMTP服务器
        $this->mailer->SMTPAuth = true;   // SMTP认证
        $this->mailer->Username = $this->config['username'];   // SMTP账号
        $this->mailer->Password = $this->config['password'];   // SMTP密码
        $this->mailer->CharSet = $this->config['charset'];   // 字符集
        $this->mailer->isHTML(true);   // HTML邮件
        $this->mailer->Port = $this->config['port'];   // SMTP端口
        if ($this->config['encryption'] === 'ssl') {   // 加密方式
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($this->config['encryption'] === 'tls') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $this->mailer->SMTPDebug = $this->config['debug'] ? 2 : 0;   // 调试模式
        $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);   // 设置发件人
    }
    
    /**
     * 发送邮件
     * @param string|array $to 收件人
     * @param string $subject 标题
     * @param string $body HTML正文
     * @param string|null $altBody 纯文本正文
     * @param array $options 选项
     * @return bool
     * @throws Exception
     */
    public function send(string|array $to, string $subject, string $body, ?string $altBody = null, array $options = [] ): bool
    {
        $this->mailer->clearAddresses();   // 清理收件人
        $this->mailer->clearAttachments();   // 清理附件
        $this->mailer->clearCCs();   // 清理 CC
        $this->mailer->clearBCCs();   // 清理 BCC
        $this->mailer->clearReplyTos();   // 清理 Reply-To
        
        if (is_array($to)) {
            foreach ($to as $email => $name) {
                if (is_string($email)) {   // ['123@qq.com' => '张三', '456@qq.com' => '李四']
                    $this->mailer->addAddress($email, $name);
                } else {   // ['123@qq.com', '456@qq.com']
                    $this->mailer->addAddress($name);
                }
            }
        } else {
            $this->mailer->addAddress($to);
        }
        
        if (!empty($options['cc'])) {   // CC 抄送
            $cc = $options['cc'];
            if (is_array($cc)) {
                foreach ($cc as $email) {
                    $this->mailer->addCC($email);
                }
            } else {
                $this->mailer->addCC($cc);
            }
        }
        
        if (!empty($options['bcc'])) {   // BCC 密送
            $bcc = $options['bcc'];
            if (is_array($bcc)) {
                foreach ($bcc as $email) {
                    $this->mailer->addBCC($email);
                }
            } else {
                $this->mailer->addBCC($bcc);
            }
        }
        
        if (!empty($options['reply_to'])) {   // Reply-To 回复地址
            $this->mailer->addReplyTo($options['reply_to'], $options['reply_name'] ?? '');
        }
        
        $this->mailer->Subject = $subject;   //标题
        $this->mailer->Body = $body;   //HTML正文
        $this->mailer->AltBody = $altBody ?? strip_tags($body);   //纯文本正文
        
        if (!empty($options['attachments'])) {   // 附件
            foreach ($options['attachments'] as $attachment) {
                if (is_array($attachment)) {
                    $path = $attachment['path'] ?? '';
                    $name = $attachment['name'] ?? '';
                    if ($path) {
                        $this->mailer->addAttachment($path, $name);
                    }
                } else {
                    $this->mailer->addAttachment($attachment);
                }
            }
        }
        
        return $this->mailer->send();   // 发送
    }
}
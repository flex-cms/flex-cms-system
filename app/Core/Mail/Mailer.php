<?php

namespace Flex\Core\Mail;

use Flex\Models\Setting;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Flex\Core\Routing\View;

class Mailer
{
    protected string $to = '';
    protected string $subject = '';
    protected string $body = '';

    protected string $fromEmail = 'info@kriskata.com';
    protected string $fromName = 'Flex CMS';

    public function __construct()
    {
        $this->fromEmail = Setting::getValue('from_email', $_ENV['MAIL_FROM_ADDRESS'] ?? 'info@kriskata.com');
        $this->fromName = Setting::getValue('site_name', $_ENV['MAIL_FROM_NAME'] ?? 'Flex CMS');
    }

    public static function to(string $email): self
    {
        $instance = new self();
        $instance->to = $email;
        return $instance;
    }

    public function from(string $email, string $name = ''): self
    {
        $this->fromEmail = $email;
        if ($name)
            $this->fromName = $name;
        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function template(string $viewPath, array $data = []): self
    {
        ob_start();
        View::component($viewPath, $data, 'components/emails');
        $this->body = ob_get_clean();

        return $this;
    }

    public function body(string $htmlContent): self
    {
        $this->body = $htmlContent;
        return $this;
    }

    public function withVariables(array $variables): self
    {
        foreach ($variables as $key => $value) {
            $this->body = str_replace('{{' . $key . '}}', $value, $this->body);
        }
        return $this;
    }

    public function send(): bool
    {
        if (empty($this->to) || empty($this->subject) || empty($this->body)) {
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();

            $mail->Host = Setting::getValue('smtp_host', $_ENV['MAIL_HOST'] ?? '');
            $mail->SMTPAuth = true;
            $mail->Username = Setting::getValue('smtp_user', $_ENV['MAIL_USERNAME'] ?? '');
            $mail->Password = Setting::getValue('smtp_pass', $_ENV['MAIL_PASSWORD'] ?? '');

            $encryption = Setting::getValue('smtp_encryption', 'tls');
            $mail->SMTPSecure = ($encryption === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

            $mail->Port = (int) Setting::getValue('smtp_port', $_ENV['MAIL_PORT'] ?? 587);
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($this->to);

            $mail->isHTML(true);
            $mail->Subject = $this->subject;
            $mail->Body = $this->body;

            return $mail->send();
        } catch (\Exception $e) {
            throw new \Exception("Mailer Error: " . $mail->ErrorInfo);
        }
    }
}

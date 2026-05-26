<?php

namespace Flex\Core\Mail;

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
        $this->fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? $this->fromEmail;
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? $this->fromName;
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
            $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.example.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'] ?? 'your-email@example.com';
            $mail->Password = $_ENV['MAIL_PASSWORD'] ?? 'your-password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'] ?? 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($this->to);

            $mail->isHTML(true);
            $mail->Subject = $this->subject;
            $mail->Body = $this->body;

            return $mail->send();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

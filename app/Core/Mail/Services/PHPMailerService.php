<?php

namespace Flex\Core\Mail\Services;

use Flex\Core\Mail\Interfaces\MailerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PHPMailerService implements MailerInterface
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        
        $this->mailer->Host       = $_ENV['MAIL_HOST'] ?? 'localhost';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $this->mailer->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->mailer->Port       = $_ENV['MAIL_PORT'] ?? 587;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        
        $this->mailer->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@yourdomain.com', 
            $_ENV['MAIL_FROM_NAME'] ?? 'Flex CMS'
        );
        
        $this->mailer->isHTML(true);
    }

    public function send(string $to, string $subject, string $templateContent, array $data = []): bool
    {
        try {
            $body = EmailService::render($templateContent, $data);

            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            return $this->mailer->send();
        } catch (Exception $e) {
            return false;
        }
    }
}
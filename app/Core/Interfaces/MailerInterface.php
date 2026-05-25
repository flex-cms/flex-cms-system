<?php

namespace Core\Interfaces;

interface MailerInterface
{
    public function send(string $to, string $subject, string $body, array $data = []): bool;
}

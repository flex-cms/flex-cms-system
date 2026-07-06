<?php

namespace Flex\Core\Mail\Interfaces;

interface MailerInterface
{
    public function send(string $to, string $subject, string $body, array $data = []): bool;
}

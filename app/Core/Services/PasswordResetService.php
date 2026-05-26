<?php

namespace Flex\Core\Services;

use Flex\Models\User;
use Flex\Models\PasswordReset;
use Flex\Core\Mail\Mailer;

class PasswordResetService
{
    public function handle(string $email): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $latestReset = PasswordReset::where('email', $email)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($latestReset && $latestReset->created_at) {
            $lastRequestedTime = strtotime($latestReset->created_at);
            $timePassed = time() - $lastRequestedTime;

            if ($timePassed >= 0 && $timePassed < 300) {
                $secondsLeft = 300 - $timePassed;
                $minutesLeft = ceil($secondsLeft / 60);

                throw new \Exception("Моля, изчакайте {$minutesLeft} мин. преди да поискате нов линк.");
            }
        }

        $token = bin2hex(random_bytes(32));
        $resetLink = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/password/forgot-form?token=" . $token;

        PasswordReset::where('email', $email)->delete();

        $passwordReset = new PasswordReset();
        $passwordReset->email = $email;
        $passwordReset->token = $token;
        $passwordReset->expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $passwordReset->created_at = date('Y-m-d H:i:s');
        $passwordReset->save();

        return Mailer::to($email)
            ->subject('Възстановяване на парола - Flex CMS')
            ->template('password-reset', [
                'title' => 'Възстановяване на парола',
                'subComponent' => 'password-reset',
                'subData' => ['resetLink' => $resetLink]
            ])
            ->send();
    }
}

<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $fillable = [
        'email',
        'token',
        'created_at',
        'expires_at'
    ];

    public $timestamps = false;

    protected $primaryKey = 'email';

    public $incrementing = false;

    protected $keyType = 'string';

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return true;
        }

        $nowUtc = gmdate('Y-m-d H:i:s');

        return strtotime($this->expires_at) < strtotime($nowUtc);
    }

    public static function deleteExistingForEmail(string $email): void
    {
        self::where('email', $email)->delete();
    }
}

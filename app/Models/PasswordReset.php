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

    public static function checkToken(string $token): self|false
    {
        $record = static::where('token', $token)->first();

        if (!$record || !$record->expires_at) {
            return false;
        }

        $nowUtc = gmdate('Y-m-d H:i:s');

        if (strtotime($record->expires_at) < strtotime($nowUtc)) {
            return false;
        }

        return $record;
    }

    public static function deleteExistingForEmail(string $email): void
    {
        self::where('email', $email)->delete();
    }
}
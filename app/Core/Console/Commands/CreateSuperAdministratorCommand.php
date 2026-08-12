<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Features\Authentication\Models\User;
use Illuminate\Database\Capsule\Manager as Capsule;
use InvalidArgumentException;
use RuntimeException;

final class CreateSuperAdministratorCommand implements CommandInterface
{
    private const MINIMUM_PASSWORD_LENGTH = 12;

    public function getName(): string
    {
        return 'auth:create-super-admin';
    }

    public function getDescription(): string
    {
        return 'Създава единствения супер администратор на Flex CMS.';
    }

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments): int
    {
        $options = $this->parseOptions($arguments);

        $this->assertDatabaseIsReady();

        if (User::query()->where('is_super_admin', true)->exists()) {
            throw new RuntimeException(
                'В системата вече има супер администратор.'
            );
        }

        $name = trim($options['name'] ?? $this->ask('Име'));
        $email = strtolower(trim($options['email'] ?? $this->ask('Имейл')));
        $password = $options['password'] ?? $this->askHidden('Парола');
        $confirmation = $options['password'] ?? $this->askHidden('Повторете паролата');

        $this->validateInput($name, $email, $password, $confirmation);

        if (User::query()->where('email', $email)->exists()) {
            throw new InvalidArgumentException(
                'Вече има потребител с този имейл адрес.'
            );
        }

        $user = Capsule::connection()->transaction(
            static function () use ($name, $email, $password): User {
                if (User::query()->where('is_super_admin', true)->lockForUpdate()->exists()) {
                    throw new RuntimeException(
                        'В системата вече има супер администратор.'
                    );
                }

                return User::query()->create([
                    'fullname' => $name,
                    'email' => $email,
                    'password' => $password,
                    'is_active' => true,
                    'is_super_admin' => true,
                ]);
            }
        );

        echo PHP_EOL;
        echo 'Супер администраторът е създаден успешно.' . PHP_EOL;
        echo sprintf('ID: %d%s', $user->id, PHP_EOL);
        echo sprintf('Име: %s%s', $user->fullname, PHP_EOL);
        echo sprintf('Имейл: %s%s', $user->email, PHP_EOL);
        echo PHP_EOL;

        return 0;
    }

    /** @param array<int, string> $arguments
     *  @return array{name?: string, email?: string, password?: string}
     */
    private function parseOptions(array $arguments): array
    {
        $options = [];

        foreach ($arguments as $argument) {
            if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
                throw new InvalidArgumentException(
                    'Използвайте: php flex auth:create-super-admin '
                    . '[--name="Име"] [--email="email@example.com"] [--password="парола"]'
                );
            }

            [$name, $value] = explode('=', substr($argument, 2), 2);

            if (!in_array($name, ['name', 'email', 'password'], true)) {
                throw new InvalidArgumentException(
                    sprintf('Непозната опция: --%s', $name)
                );
            }

            $options[$name] = $value;
        }

        return $options;
    }

    private function assertDatabaseIsReady(): void
    {
        $schema = Capsule::schema();

        if (!$schema->hasTable('authentication_users')) {
            throw new RuntimeException('Таблицата authentication_users не съществува. Изпълнете миграциите.');
        }

        foreach (['is_super_admin', 'super_admin_slot'] as $column) {
            if (!$schema->hasColumn('authentication_users', $column)) {
                throw new RuntimeException(
                    sprintf(
                        'Колоната users.%s липсва. Изпълнете Authentication миграциите.',
                        $column
                    )
                );
            }
        }
    }

    private function validateInput(
        string $name,
        string $email,
        string $password,
        string $confirmation
    ): void {
        if ($name === '') {
            throw new InvalidArgumentException('Името е задължително.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Имейл адресът е невалиден.');
        }

        if (strlen($password) < self::MINIMUM_PASSWORD_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Паролата трябва да бъде поне %d символа.', self::MINIMUM_PASSWORD_LENGTH)
            );
        }

        if (!hash_equals($password, $confirmation)) {
            throw new InvalidArgumentException('Паролите не съвпадат.');
        }
    }

    private function ask(string $question): string
    {
        echo sprintf('%s: ', $question);
        $answer = fgets(STDIN);

        if ($answer === false) {
            throw new RuntimeException('Неуспешно прочитане от конзолата.');
        }

        return rtrim($answer, "\r\n");
    }

    private function askHidden(string $question): string
    {
        echo sprintf('%s: ', $question);

        if (PHP_OS_FAMILY === 'Windows') {
            $command = 'powershell -NoProfile -Command '
                . '"$secure = Read-Host -AsSecureString; '
                . '$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure); '
                . 'try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer) } '
                . 'finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer) }"';
            $password = shell_exec($command);

            if (!is_string($password)) {
                throw new RuntimeException('Неуспешно прочитане на скритата парола.');
            }

            return rtrim($password, "\r\n");
        }

        $sttyMode = shell_exec('stty -g');
        shell_exec('stty -echo');

        try {
            $password = fgets(STDIN);
        } finally {
            if (is_string($sttyMode) && trim($sttyMode) !== '') {
                shell_exec('stty ' . escapeshellarg(trim($sttyMode)));
            } else {
                shell_exec('stty echo');
            }
            echo PHP_EOL;
        }

        if ($password === false) {
            throw new RuntimeException('Неуспешно прочитане на скритата парола.');
        }

        return rtrim($password, "\r\n");
    }
}

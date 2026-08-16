<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Factories;

use Flex\Core\Database\Factory;
use Flex\Features\Authentication\Models\User;

final class UserFactory extends Factory
{
    protected string $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker()->name(),
            'email' => $this->faker()->unique()->safeEmail(),
            'password' => password_hash(
                'password',
                PASSWORD_DEFAULT
            ),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}

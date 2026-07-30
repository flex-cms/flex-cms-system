<?php

declare(strict_types=1);

namespace Flex\Core\Console;

interface CommandInterface
{
    /**
     * Името, чрез което командата се извиква.
     *
     * Например: plugin:migrate
     */
    public function getName(): string;

    /**
     * Кратко описание на командата.
     */
    public function getDescription(): string;

    /**
     * Изпълнява командата.
     *
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments): int;
}
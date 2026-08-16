<?php

declare(strict_types=1);

namespace Flex\Core\Console;

use Flex\Core\Console\Commands\CreateSuperAdministratorCommand;
use Flex\Core\Console\Commands\DatabaseSeedCommand;
use Flex\Core\Console\Commands\FeatureFreshCommand;
use Flex\Core\Console\Commands\FeatureRollbackCommand;
use Flex\Core\Console\Commands\FeatureMakeMigrationCommand;
use Flex\Core\Console\Commands\FeatureMigrateCommand;
use Flex\Core\Console\Commands\FeatureStatusCommand;
use Flex\Core\Console\Commands\PluginFreshCommand;
use Flex\Core\Console\Commands\PluginMigrateCommand;
use Flex\Core\Console\Commands\PluginRollbackCommand;
use Flex\Core\Console\Commands\PluginStatusCommand;
use Throwable;

final class Application
{
    /**
     * @var array<string, CommandInterface>
     */
    private array $commands = [];

    public function __construct(
        private readonly string $projectPath
    ) {
        $this->registerDefaultCommands();
    }

    /**
     * @param array<int, string> $argv
     */
    public function run(array $argv): int
    {
        $commandName = $argv[1] ?? null;

        if (
            $commandName === null || in_array(
                $commandName,
                ['list', '--help', '-h'],
                true
            )
        ) {
            $this->displayCommands();

            return 0;
        }

        if (!isset($this->commands[$commandName])) {
            $this->error(
                sprintf(
                    'Командата "%s" не съществува.',
                    $commandName
                )
            );

            $this->displayCommands();

            return 1;
        }

        $arguments = array_slice($argv, 2);

        try {
            return $this->commands[$commandName]
                ->handle($arguments);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            if ($this->isDebugEnabled()) {
                echo PHP_EOL;
                echo $exception;
                echo PHP_EOL;
            }

            return 1;
        }
    }

    private function registerDefaultCommands(): void
    {
        $this->register(
            new CreateSuperAdministratorCommand()
        );

        $this->register(
            new PluginMigrateCommand(
                projectPath: $this->projectPath
            )
        );

        $this->register(
            new PluginRollbackCommand(
                projectPath: $this->projectPath
            )
        );

        $this->register(
            new PluginStatusCommand(
                projectPath: $this->projectPath
            )
        );

        $this->register(
            new PluginFreshCommand(
                projectPath: $this->projectPath
            )
        );


        $this->register(
            new FeatureMakeMigrationCommand(
                projectPath: $this->projectPath
            )
        );

        $this->register(
            new FeatureMigrateCommand(
                projectPath: $this->projectPath
            )
        );

        $this->register(
            new FeatureStatusCommand(
                projectPath: $this->projectPath
            )
        );


        $this->register(
            new FeatureRollbackCommand(
                projectPath: $this->projectPath
            )
        );


        $this->register(
            new FeatureFreshCommand(
                projectPath: $this->projectPath
            )
        );


        $this->register(
            new DatabaseSeedCommand(
                projectPath: $this->projectPath
            )
        );
    }

    private function register(
        CommandInterface $command
    ): void {
        $this->commands[$command->getName()] = $command;
    }

    private function displayCommands(): void
    {
        echo PHP_EOL;
        echo "Flex CMS Console";
        echo PHP_EOL;
        echo str_repeat('=', 40);
        echo PHP_EOL;
        echo PHP_EOL;
        echo "Използване:";
        echo PHP_EOL;
        echo "  php flex <command> [arguments]";
        echo PHP_EOL;
        echo PHP_EOL;
        echo "Налични команди:";
        echo PHP_EOL;

        foreach ($this->commands as $command) {
            echo sprintf(
                "  %-24s %s%s",
                $command->getName(),
                $command->getDescription(),
                PHP_EOL
            );
        }

        echo PHP_EOL;
    }

    private function error(string $message): void
    {
        fwrite(
            STDERR,
            sprintf(
                "Грешка: %s%s",
                $message,
                PHP_EOL
            )
        );
    }

    private function isDebugEnabled(): bool
    {
        $debug = $_ENV['APP_DEBUG']
            ?? $_SERVER['APP_DEBUG']
            ?? getenv('APP_DEBUG');

        return filter_var(
            $debug,
            FILTER_VALIDATE_BOOL
        );
    }
}

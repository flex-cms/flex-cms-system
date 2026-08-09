<?php

declare(strict_types=1);

namespace Tests\Unit\Container;

use Flex\Core\Container\Container;
use Flex\Core\Container\Exceptions\CircularDependencyException;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function testItAutomaticallyBuildsNestedDependencies(): void
    {
        $container = new Container();
        $service = $container->make(ExampleService::class);

        self::assertInstanceOf(ExampleRepository::class, $service->repository);
    }

    public function testItResolvesAnInterfaceBinding(): void
    {
        $container = new Container();
        $container->bind(RepositoryContract::class, ExampleRepository::class);

        self::assertInstanceOf(ExampleRepository::class, $container->make(RepositoryContract::class));
    }

    public function testSingletonReturnsTheSameInstance(): void
    {
        $container = new Container();
        $container->singleton(ExampleRepository::class);

        self::assertSame(
            $container->make(ExampleRepository::class),
            $container->make(ExampleRepository::class),
        );
    }

    public function testFactoryMayReceiveTheContainer(): void
    {
        $container = new Container();
        $container->bind('answer', static fn (Container $container): Answer => new Answer(42));

        self::assertSame(42, $container->make('answer')->value);
    }

    public function testItInjectsMethodDependenciesAndNamedParameters(): void
    {
        $container = new Container();
        $result = $container->call([ExampleController::class, 'show'], ['id' => 15]);

        self::assertSame('15:repository', $result);
    }

    public function testItDetectsCircularDependencies(): void
    {
        $container = new Container();

        $this->expectException(CircularDependencyException::class);
        $container->make(CircularA::class);
    }
}

interface RepositoryContract
{
    public function name(): string;
}

final class ExampleRepository implements RepositoryContract
{
    public function name(): string
    {
        return 'repository';
    }
}

final readonly class ExampleService
{
    public function __construct(public ExampleRepository $repository)
    {
    }
}

final readonly class Answer
{
    public function __construct(public int $value)
    {
    }
}

final class ExampleController
{
    public function show(ExampleRepository $repository, int $id): string
    {
        return $id . ':' . $repository->name();
    }
}

final class CircularA
{
    public function __construct(CircularB $dependency)
    {
    }
}

final class CircularB
{
    public function __construct(CircularA $dependency)
    {
    }
}
